<?php

namespace Tests\Feature\Http;

use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class InvoicesEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_invoices_sorted_by_created_at_desc_by_default(): void
    {
        $older = Invoice::factory()->create(['created_at' => now()->subDay()]);
        $newer = Invoice::factory()->create(['created_at' => now()]);

        $response = $this->getJson('/api/invoices');

        $response->assertStatus(200);
        $this->assertSame(
            [$newer->id, $older->id],
            collect($response->json('data'))->pluck('id')->all()
        );
    }

    public function test_it_sorts_by_due_date_ascending(): void
    {
        $later = Invoice::factory()->create(['due_date' => '2026-12-01']);
        $earlier = Invoice::factory()->create(['due_date' => '2026-01-01']);

        $response = $this->getJson('/api/invoices?sort=due_date&direction=asc');

        $this->assertSame(
            [$earlier->id, $later->id],
            collect($response->json('data'))->pluck('id')->all()
        );
    }

    public function test_it_sorts_by_due_date_descending(): void
    {
        $later = Invoice::factory()->create(['due_date' => '2026-12-01']);
        $earlier = Invoice::factory()->create(['due_date' => '2026-01-01']);

        $response = $this->getJson('/api/invoices?sort=due_date&direction=desc');

        $this->assertSame(
            [$later->id, $earlier->id],
            collect($response->json('data'))->pluck('id')->all()
        );
    }

    public function test_it_ignores_unknown_sort_column(): void
    {
        Invoice::factory()->count(2)->create();

        $response = $this->getJson('/api/invoices?'.http_build_query([
            'sort' => '1); DROP TABLE invoices; --',
        ]));

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    public function test_it_ignores_array_sort_param_instead_of_crashing(): void
    {
        Invoice::factory()->count(2)->create();

        $response = $this->getJson('/api/invoices?sort[]=x&direction[]=y');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    public function test_it_paginates_results(): void
    {
        Invoice::factory()->count(20)->create();

        $first = $this->getJson('/api/invoices?page=1');
        $first->assertJsonPath('meta.per_page', 15);
        $first->assertJsonPath('meta.last_page', 2);
        $first->assertJsonCount(15, 'data');

        $second = $this->getJson('/api/invoices?page=2');
        $second->assertJsonCount(5, 'data');
    }

    public function test_it_returns404_for_unknown_invoice(): void
    {
        $response = $this->getJson('/api/invoices/999999');

        $response->assertStatus(404);
    }

    public function test_it_rejects_duplicate_invoice_number_on_create(): void
    {
        Invoice::factory()->create(['number' => 'INV-DUPLICATE']);

        $response = $this->postJson('/api/invoices', $this->validInvoicePayload(['number' => 'INV-DUPLICATE']));

        $response->assertStatus(422);
        $this->assertSame(1, Invoice::query()->where('number', 'INV-DUPLICATE')->count());
    }

    public function test_it_computes_gross_amount_server_side_on_create(): void
    {
        $response = $this->postJson('/api/invoices', $this->validInvoicePayload([
            'net_amount' => 100,
            'vat_amount' => 20,
            'gross_amount' => 999999,
        ]));

        $response->assertStatus(201);
        $response->assertJsonPath('data.gross_amount', '120.00');
    }

    public function test_it_rejects_net_amount_exceeding_column_capacity(): void
    {
        $response = $this->postJson('/api/invoices', $this->validInvoicePayload([
            'net_amount' => 99999999999,
        ]));

        $response->assertStatus(422);
    }

    public function test_it_rejects_due_date_before_issue_date_on_create(): void
    {
        $response = $this->postJson('/api/invoices', $this->validInvoicePayload([
            'issue_date' => '2026-06-10',
            'due_date' => '2026-06-01',
        ]));

        $response->assertStatus(422);
    }

    public function test_it_updates_pending_invoice_and_recalculates_gross_amount(): void
    {
        $invoice = Invoice::factory()->create(['status' => 'pending', 'net_amount' => 100, 'vat_amount' => 20]);

        $response = $this->putJson("/api/invoices/{$invoice->id}", [
            'net_amount' => 200,
            'vat_amount' => 50,
            'due_date' => $invoice->due_date->format('Y-m-d'),
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.gross_amount', '250.00');
    }

    public function test_it_ignores_client_supplied_gross_amount_on_update(): void
    {
        $invoice = Invoice::factory()->create(['status' => 'pending']);

        $response = $this->putJson("/api/invoices/{$invoice->id}", [
            'net_amount' => 10,
            'vat_amount' => 5,
            'gross_amount' => 999999,
            'due_date' => $invoice->due_date->format('Y-m-d'),
        ]);

        $response->assertJsonPath('data.gross_amount', '15.00');
    }

    public function test_it_rejects_due_date_before_issue_date_on_update(): void
    {
        $invoice = Invoice::factory()->create(['status' => 'pending', 'issue_date' => '2026-06-01']);

        $response = $this->putJson("/api/invoices/{$invoice->id}", [
            'net_amount' => 10,
            'vat_amount' => 5,
            'due_date' => '2026-05-01',
        ]);

        $response->assertStatus(422);
    }

    public function test_it_returns409_when_updating_non_pending_invoice(): void
    {
        $invoice = Invoice::factory()->create(['status' => 'approved', 'net_amount' => 100, 'vat_amount' => 20]);

        $response = $this->putJson("/api/invoices/{$invoice->id}", [
            'net_amount' => 999,
            'vat_amount' => 999,
            'due_date' => $invoice->due_date->format('Y-m-d'),
        ]);

        $response->assertStatus(409);
        $this->assertSame('100.00', $invoice->fresh()->net_amount);
    }

    public function test_unexpected_exception_is_logged_and_returns_clean_server_error(): void
    {
        $this->mock(InvoiceRepository::class, function ($mock) {
            $mock->shouldReceive('paginate')->andThrow(new \RuntimeException('unexpected failure'));
        });

        Log::spy();

        $response = $this->getJson('/api/invoices');

        $response->assertStatus(500);
        $response->assertJson(['message' => 'Сталася непередбачена помилка на сервері. Спробуйте ще раз пізніше.']);
        $response->assertDontSee('RuntimeException');
        $response->assertDontSee('unexpected failure');
        Log::shouldHaveReceived('error')->once();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validInvoicePayload(array $overrides = []): array
    {
        return array_merge([
            'number' => 'INV-'.fake()->unique()->numerify('####'),
            'supplier_name' => 'Acme LLC',
            'supplier_tax_id' => '1234567890',
            'net_amount' => 100,
            'vat_amount' => 20,
            'currency' => 'UAH',
            'issue_date' => '2026-06-01',
            'due_date' => '2026-06-15',
        ], $overrides);
    }
}

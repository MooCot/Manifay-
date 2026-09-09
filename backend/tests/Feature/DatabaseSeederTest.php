<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_is_idempotent_on_repeated_runs(): void
    {
        $this->seed(DatabaseSeeder::class);
        $invoiceCount = Invoice::query()->count();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($invoiceCount, Invoice::query()->count());
    }
}

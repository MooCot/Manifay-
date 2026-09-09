<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $netAmount = $this->faker->randomFloat(2, 100, 50000);
        $vatAmount = round($netAmount * 0.2, 2);
        $issueDate = $this->faker->dateTimeBetween('-30 days', 'now');
        $dueDate = (clone $issueDate)->modify('+'.$this->faker->numberBetween(7, 30).' days');

        return [
            'number' => strtoupper($this->faker->unique()->bothify('INV-####-???')),
            'supplier_name' => $this->faker->company(),
            'supplier_tax_id' => $this->faker->numerify('##########'),
            'net_amount' => $netAmount,
            'vat_amount' => $vatAmount,
            'gross_amount' => round($netAmount + $vatAmount, 2),
            'currency' => 'UAH',
            'status' => InvoiceStatus::Pending->value,
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => InvoiceStatus::Approved->value]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status' => InvoiceStatus::Rejected->value]);
    }
}

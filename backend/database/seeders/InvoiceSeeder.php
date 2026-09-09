<?php

namespace Database\Seeders;

use App\Models\Invoice;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        if (Invoice::query()->exists()) {
            return;
        }

        Invoice::factory()->count(30)->create();
        Invoice::factory()->count(10)->approved()->create();
        Invoice::factory()->count(8)->rejected()->create();
    }
}

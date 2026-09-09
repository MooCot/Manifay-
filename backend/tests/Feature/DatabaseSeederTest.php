<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_is_idempotent_on_repeated_runs(): void
    {
        $this->seed(DatabaseSeeder::class);
        $userCount = User::query()->count();
        $invoiceCount = Invoice::query()->count();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($userCount, User::query()->count());
        $this->assertSame($invoiceCount, Invoice::query()->count());
    }
}

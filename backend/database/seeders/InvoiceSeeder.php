<?php

namespace Database\Seeders;

use App\Models\Invoice;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Немає create-форми на фронтенді і немає ендпоінту зміни статусу
     * (див. CLAUDE.md "Архітектурні рішення" п.1-2) — тому демо-дані,
     * включно з approved/rejected інвойсами, генеруються тут, щоб на
     * реальному прикладі можна було продемонструвати заблоковану edit-форму.
     */
    public function run(): void
    {
        // ідемпотентність: docker-compose викликає db:seed при кожному
        // старті контейнера (persisted db_data volume), не плодимо дублі
        if (Invoice::query()->exists()) {
            return;
        }

        Invoice::factory()->count(6)->create();
        Invoice::factory()->count(3)->approved()->create();
        Invoice::factory()->count(2)->rejected()->create();
    }
}

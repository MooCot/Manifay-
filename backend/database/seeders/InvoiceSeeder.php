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

        // 48 інвойсів (per_page=15 → 4 сторінки), щоб було на чому
        // продемонструвати пагінацію на фронтенді
        Invoice::factory()->count(30)->create();
        Invoice::factory()->count(10)->approved()->create();
        Invoice::factory()->count(8)->rejected()->create();
    }
}

<?php

namespace App\Repositories;

use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InvoiceRepository
{
    // менше рядків на сторінку — таблиця комфортно влазить на екран без
    // прокрутки на типовій висоті viewport (замість динамічного розрахунку
    // під висоту вікна, що крихкіше й вимагає resize-логіки на фронтенді)
    public function paginate(int $perPage = 8): LengthAwarePaginator
    {
        return Invoice::query()->orderByDesc('created_at')->paginate($perPage);
    }

    public function findOrFail(int $id): Invoice
    {
        return Invoice::query()->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Invoice
    {
        return Invoice::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Invoice $invoice, array $attributes): Invoice
    {
        $invoice->update($attributes);

        return $invoice;
    }
}

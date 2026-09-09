<?php

namespace App\Repositories;

use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InvoiceRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        // id як tiebreak: без нього рядки з однаковим created_at (наприклад,
        // масово насіджені за одну секунду) повертаються в недетермінованому
        // порядку — теоретично можуть "зникнути"/здублюватись між сторінками
        return Invoice::query()->orderByDesc('created_at')->orderByDesc('id')->paginate($perPage);
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

<?php

namespace App\Repositories;

use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InvoiceRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
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

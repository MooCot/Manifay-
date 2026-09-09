<?php

namespace App\Repositories;

use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InvoiceRepository
{
    /**
     * @var list<string>
     */
    private const SORTABLE_COLUMNS = ['created_at', 'due_date'];

    public function paginate(int $perPage = 15, string $sortBy = 'created_at', string $sortDirection = 'desc'): LengthAwarePaginator
    {
        // whitelist — sortBy йде в orderBy() напряму, не можна довіряти
        // довільному значенню з query-рядка
        if (! in_array($sortBy, self::SORTABLE_COLUMNS, true)) {
            $sortBy = 'created_at';
        }
        $sortDirection = $sortDirection === 'asc' ? 'asc' : 'desc';

        // id як tiebreak: без нього рядки з однаковим значенням сортування
        // (наприклад, масово насіджені за одну секунду created_at) повертаються
        // в недетермінованому порядку — теоретично можуть "зникнути"/здублюватись
        // між сторінками
        return Invoice::query()
            ->orderBy($sortBy, $sortDirection)
            ->orderByDesc('id')
            ->paginate($perPage);
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

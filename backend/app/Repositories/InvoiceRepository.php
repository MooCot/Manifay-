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
        if (! in_array($sortBy, self::SORTABLE_COLUMNS, true)) {
            $sortBy = 'created_at';
        }
        $sortDirection = $sortDirection === 'asc' ? 'asc' : 'desc';

        return Invoice::query()
            ->orderBy($sortBy, $sortDirection)
            ->orderByDesc('id')
            ->paginate($perPage);
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

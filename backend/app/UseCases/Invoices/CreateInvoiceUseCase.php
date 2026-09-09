<?php

namespace App\UseCases\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Repositories\InvoiceRepository;

class CreateInvoiceUseCase
{
    public function __construct(private readonly InvoiceRepository $invoices) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes): Invoice
    {
        $attributes['gross_amount'] = round((float) $attributes['net_amount'] + (float) $attributes['vat_amount'], 2);
        $attributes['status'] = InvoiceStatus::Pending->value;

        return $this->invoices->create($attributes);
    }
}

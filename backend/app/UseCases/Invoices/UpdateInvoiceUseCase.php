<?php

namespace App\UseCases\Invoices;

use App\Enums\InvoiceStatus;
use App\Exceptions\InvoiceNotEditableException;
use App\Models\Invoice;
use App\Repositories\InvoiceRepository;

class UpdateInvoiceUseCase
{
    public function __construct(private readonly InvoiceRepository $invoices) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Invoice $invoice, array $attributes): Invoice
    {
        if ($invoice->status !== InvoiceStatus::Pending) {
            throw new InvoiceNotEditableException(
                "Invoice {$invoice->id} cannot be edited: status is \"{$invoice->status->value}\", only \"pending\" invoices are editable."
            );
        }

        $attributes['gross_amount'] = round((float) $attributes['net_amount'] + (float) $attributes['vat_amount'], 2);

        return $this->invoices->update($invoice, $attributes);
    }
}

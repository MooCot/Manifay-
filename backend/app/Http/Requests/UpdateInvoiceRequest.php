<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Invoice $invoice */
        $invoice = $this->route('invoice');

        return [
            'net_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'vat_amount' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'due_date' => ['required', 'date', 'after_or_equal:'.$invoice->issue_date->format('Y-m-d')],
        ];
    }
}

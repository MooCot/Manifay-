<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // аутентифікація не потрібна за ТЗ
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'number' => ['required', 'string', 'max:255', 'unique:invoices,number'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'supplier_tax_id' => ['required', 'string', 'max:255'],
            'net_amount' => ['required', 'numeric', 'min:0.01'],
            'vat_amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
        ];
    }
}

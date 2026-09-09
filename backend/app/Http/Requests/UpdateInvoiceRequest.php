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
     * issue_date не редагується (див. CLAUDE.md "Архітектурні рішення" п.4),
     * тому due_date звіряється проти зафіксованого issue_date наявного інвойса,
     * а не проти поля з payload.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Invoice $invoice */
        $invoice = $this->route('invoice');

        return [
            // max — стеля decimal(12,2) колонки (міграція): без цього значення
            // за межею колонки падали в 500/400 замість чистого 422
            'net_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'vat_amount' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'due_date' => ['required', 'date', 'after_or_equal:'.$invoice->issue_date->format('Y-m-d')],
        ];
    }
}

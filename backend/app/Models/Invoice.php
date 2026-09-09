<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'number',
        'supplier_name',
        'supplier_tax_id',
        'net_amount',
        'vat_amount',
        'gross_amount',
        'currency',
        'status',
        'issue_date',
        'due_date',
    ];

    /**
     * Класичний $casts-масив, а не Laravel 12-івський метод casts():
     * Larastan (поточна версія) не резолвить типи властивостей із
     * методу, це каскадом ламало аналіз в UseCase/FormRequest —
     * поведінково ідентично, Laravel підтримує обидва стилі однаково.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'net_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'status' => InvoiceStatus::class,
        'issue_date' => 'date:Y-m-d',
        'due_date' => 'date:Y-m-d',
    ];
}

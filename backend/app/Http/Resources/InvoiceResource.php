<?php

namespace App\Http\Resources;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin Invoice
 */
#[OA\Schema(
    schema: 'Invoice',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'number', type: 'string', example: 'INV-0001-ABC'),
        new OA\Property(property: 'supplier_name', type: 'string', example: 'Acme LLC'),
        new OA\Property(property: 'supplier_tax_id', type: 'string', example: '1234567890'),
        new OA\Property(property: 'net_amount', type: 'string', example: '1000.00'),
        new OA\Property(property: 'vat_amount', type: 'string', example: '200.00'),
        new OA\Property(property: 'gross_amount', type: 'string', example: '1200.00'),
        new OA\Property(property: 'currency', type: 'string', example: 'UAH'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'approved', 'rejected'], example: 'pending'),
        new OA\Property(property: 'issue_date', type: 'string', format: 'date', example: '2026-06-01'),
        new OA\Property(property: 'due_date', type: 'string', format: 'date', example: '2026-06-15'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
)]
class InvoiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'supplier_name' => $this->supplier_name,
            'supplier_tax_id' => $this->supplier_tax_id,
            'net_amount' => (string) $this->net_amount,
            'vat_amount' => (string) $this->vat_amount,
            'gross_amount' => (string) $this->gross_amount,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'issue_date' => $this->issue_date->format('Y-m-d'),
            'due_date' => $this->due_date->format('Y-m-d'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

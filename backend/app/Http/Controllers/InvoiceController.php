<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use App\UseCases\Invoices\CreateInvoiceUseCase;
use App\UseCases\Invoices\UpdateInvoiceUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class InvoiceController extends Controller
{
    public function index(Request $request, InvoiceRepository $invoices): JsonResponse
    {
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');

        $paginator = $invoices->paginate(
            sortBy: is_string($sort) ? $sort : 'created_at',
            sortDirection: is_string($direction) ? $direction : 'desc',
        );

        return InvoiceResource::collection($paginator)->response();
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return (new InvoiceResource($invoice))->response();
    }

    #[OA\Post(
        path: '/invoices',
        summary: 'Створити інвойс',
        tags: ['Invoices'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['number', 'supplier_name', 'supplier_tax_id', 'net_amount', 'vat_amount', 'currency', 'issue_date', 'due_date'],
                properties: [
                    new OA\Property(property: 'number', type: 'string', maxLength: 255, example: 'INV-0001-ABC'),
                    new OA\Property(property: 'supplier_name', type: 'string', maxLength: 255, example: 'Acme LLC'),
                    new OA\Property(property: 'supplier_tax_id', type: 'string', maxLength: 255, example: '1234567890'),
                    new OA\Property(property: 'net_amount', type: 'number', format: 'float', minimum: 0.01, maximum: 9999999999.99, example: 1000.00),
                    new OA\Property(property: 'vat_amount', type: 'number', format: 'float', minimum: 0, maximum: 9999999999.99, example: 200.00),
                    new OA\Property(property: 'currency', type: 'string', minLength: 3, maxLength: 3, example: 'UAH'),
                    new OA\Property(property: 'issue_date', type: 'string', format: 'date', example: '2026-06-01'),
                    new OA\Property(property: 'due_date', type: 'string', format: 'date', example: '2026-06-15', description: 'Має бути не раніше issue_date'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Інвойс створено',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Invoice')],
                ),
            ),
            new OA\Response(response: 422, description: 'Помилка валідації (номер вже існує, due_date раніше issue_date тощо)'),
        ],
    )]
    public function store(StoreInvoiceRequest $request, CreateInvoiceUseCase $useCase): JsonResponse
    {
        $invoice = $useCase->handle($request->validated());

        return (new InvoiceResource($invoice))->response()->setStatusCode(201);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice, UpdateInvoiceUseCase $useCase): JsonResponse
    {
        $invoice = $useCase->handle($invoice, $request->validated());

        return (new InvoiceResource($invoice))->response();
    }
}

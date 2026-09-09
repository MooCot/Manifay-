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

class InvoiceController extends Controller
{
    // index/show — чисте читання без бізнес-логіки, тому напряму через
    // Repository, без порожнього UseCase-делегата (див. CLAUDE.md "Архітектура бекенду")
    public function index(InvoiceRepository $invoices): JsonResponse
    {
        return InvoiceResource::collection($invoices->paginate())->response();
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return (new InvoiceResource($invoice))->response();
    }

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

<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/invoices', [InvoiceController::class, 'index']);
Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
Route::post('/invoices', [InvoiceController::class, 'store']);
Route::put('/invoices/{invoice}', [InvoiceController::class, 'update']);

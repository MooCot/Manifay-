<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/invoices', [InvoiceController::class, 'index']);
Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
Route::post('/invoices', [InvoiceController::class, 'store']);
Route::put('/invoices/{invoice}', [InvoiceController::class, 'update']);

Route::get('/openapi.json', function () {
    return response(file_get_contents(storage_path('api-docs/openapi.json')))
        ->header('Content-Type', 'application/json');
});

Route::get('/docs', function () {
    return response(<<<'HTML'
        <!doctype html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Manifay API</title>
            <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
        </head>
        <body>
            <div id="swagger-ui"></div>
            <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
            <script>
                window.ui = SwaggerUIBundle({
                    url: '/api/openapi.json',
                    dom_id: '#swagger-ui',
                });
            </script>
        </body>
        </html>
        HTML)
        ->header('Content-Type', 'text/html');
});

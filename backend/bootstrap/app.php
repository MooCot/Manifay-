<?php

use App\Exceptions\InvoiceNotEditableException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (InvoiceNotEditableException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        });

        // Усе, що інакше стало б 500 (unhandled exception): Laravel вже логує
        // його автоматично до виклику render() (kernel report()+render() —
        // окремі кроки), тому тут лише формуємо чисту, людино-зрозумілу
        // відповідь, без повторного report() — інакше подвійне логування.
        // Не займає те, що Laravel вже коректно мапить самостійно — 422
        // (ValidationException), 404 (ModelNotFoundException/HttpExceptionInterface).
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $alreadyHandled = $e instanceof HttpExceptionInterface
                || $e instanceof ValidationException
                || $e instanceof ModelNotFoundException;

            if ($alreadyHandled) {
                return null;
            }

            return response()->json([
                'message' => 'Сталася непередбачена помилка на сервері. Спробуйте ще раз пізніше.',
            ], 400);
        });
    })->create();

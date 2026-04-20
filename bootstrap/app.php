<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(fn($request, $e) => $request->is('api/*'));

        $exceptions->respond(function ($response, $e, $request) {
            if ($request->is('api/*')) {
                // Mapping specific exception types to custom messages
                $message = match (true) {
                    $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 'Resource not found',
                    $e instanceof \Illuminate\Validation\ValidationException => 'Validation failed',
                    $e instanceof \Illuminate\Auth\AuthenticationException => 'Unauthenticated',
                    $e instanceof \Illuminate\Auth\Access\AuthorizationException => 'Forbidden',
                    default => $e->getMessage() ?: 'An unexpected error occurred',
                };

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'data' => null,
                    'errors' => ($e instanceof \Illuminate\Validation\ValidationException) ? $e->errors() : null,
                ], $response->getStatusCode());
            }

            return $response;
        });

    })->create();

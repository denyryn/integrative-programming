<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(fn ($request, $e) => $request->is('api/*', 'auth/*'));

        $exceptions->respond(function ($response, $e, $request) {
            if ($request->is('api/*', 'auth/*')) {
                // Mapping specific exception types to custom messages
                $message = match (true) {
                    $e instanceof ModelNotFoundException => 'Resource not found',
                    $e instanceof NotFoundHttpException => 'Resource not found',
                    $e instanceof ValidationException => 'Validation failed',
                    $e instanceof AuthenticationException => 'Unauthenticated',
                    $e instanceof AuthorizationException => 'Forbidden',
                    $e instanceof AccessDeniedHttpException => 'Forbidden',
                    default => $e->getMessage() ?: 'An unexpected error occurred',
                };

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'data' => null,
                    'errors' => ($e instanceof ValidationException) ? $e->errors() : null,
                ], $response->getStatusCode());
            }

            return $response;
        });

    })->create();

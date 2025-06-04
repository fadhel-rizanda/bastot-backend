<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add any custom middleware here if needed
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle authentication exceptions
        $exceptions->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Authentication required. Please login or provide a valid token.',
                    'code' => 401,
                    'details' => 'The provided authentication credentials are invalid or missing.'
                ], 401);
            }
        });

        // Handle query exceptions
        $exceptions->renderable(function (QueryException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Database Error',
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ], 500);
            }
        });

        // Handle HTTP exceptions
        $exceptions->renderable(function (HttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'HTTP Error',
                    'message' => $e->getMessage(),
                    'code' => $e->getStatusCode()
                ], $e->getStatusCode());
            }
        });

        // Catch-all for other exceptions
        $exceptions->renderable(function (\Throwable $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                return response()->json([
                    'error' => 'Server Error',
                    'message' => $e->getMessage(),
                    'code' => $statusCode
                ], $statusCode);
            }
        });
    })->create();

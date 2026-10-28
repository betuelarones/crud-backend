<?php

use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($exception instanceof ValidationException) {
                return ApiResponse::error(
                    'Los datos proporcionados no son válidos.',
                    $exception->errors(),
                    422,
                );
            }

            if ($exception instanceof AuthenticationException) {
                return ApiResponse::error('No autenticado.', [], 401);
            }

            if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
                return ApiResponse::error('Recurso no encontrado.', [], 404);
            }

            if ($exception instanceof MethodNotAllowedHttpException) {
                return ApiResponse::error('Método no permitido.', [], 405);
            }

            if ($exception instanceof HttpResponseException) {
                return $exception->getResponse();
            }

            if ($exception instanceof HttpExceptionInterface) {
                return ApiResponse::error(
                    $exception->getMessage() ?: 'La solicitud no pudo procesarse.',
                    [],
                    $exception->getStatusCode(),
                );
            }

            Log::error('Error no controlado en la API', ['exception' => $exception]);

            return ApiResponse::error('Error interno del servidor.', [], 500);
        });
    })->create();

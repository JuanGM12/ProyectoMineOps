<?php

use App\Application\Activity\Exceptions\ActivityCodeAlreadyExists;
use App\Application\Activity\Exceptions\ActivityNotFound;
use App\Domain\Activity\Exceptions\DomainException;
use App\Domain\Activity\Exceptions\InvalidActivityStatusTransition;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
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
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (ActivityNotFound $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'errors' => (object) [],
            ], 404);
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'El recurso solicitado no existe.',
                'errors' => (object) [],
            ], 404);
        });

        $exceptions->render(function (ActivityCodeAlreadyExists $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'errors' => (object) [],
            ], 409);
        });

        $exceptions->render(function (InvalidActivityStatusTransition $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'errors' => (object) [],
            ], 409);
        });

        $exceptions->render(function (DomainException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'errors' => (object) [],
            ], 422);
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Los datos enviados no son válidos.',
                'errors' => $exception->errors(),
            ], 422);
        });

        $exceptions->render(function (QueryException $exception, Request $request) {
            $driverCode = (int) ($exception->errorInfo[1] ?? 0);

            if (! $request->is('api/*') || ! in_array($driverCode, [19, 1062], true)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'El registro entra en conflicto con datos existentes.',
                'errors' => (object) [],
            ], 409);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getStatusCode() === 404
                    ? 'El recurso solicitado no existe.'
                    : 'No fue posible procesar la solicitud.',
                'errors' => (object) [],
            ], $exception->getStatusCode());
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado.',
                'errors' => (object) [],
            ], 500);
        });
    })->create();

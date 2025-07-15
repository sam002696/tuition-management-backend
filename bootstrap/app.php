<?php

use App\Services\ResponseBuilder\ApiResponseService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);
        $middleware->append(\App\Http\Middleware\TrimStrings::class);
        $middleware->append(\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 401: Authentication error
        $exceptions->render(function (AuthenticationException $exception, $request) {
            return ApiResponseService::errorResponse('Invalid or missing authentication token', 401);
        });

        // 403: Forbidden (role mismatch or policy denial)
        $exceptions->render(function (AuthorizationException $exception, $request) {
            return ApiResponseService::errorResponse('You do not have permission to access this resource', 403);
        });

        // 404: Route or Model not found
        $exceptions->render(function (NotFoundHttpException | ModelNotFoundException $exception, $request) {
            return ApiResponseService::errorResponse('The requested resource was not found', 404);
        });

        // 405: Invalid HTTP method
        $exceptions->render(function (MethodNotAllowedHttpException $exception, $request) {
            return ApiResponseService::errorResponse('Method not allowed for this endpoint', 405);
        });

        // 422: Validation error
        $exceptions->render(function (ValidationException $exception, $request) {
            return ApiResponseService::handleValidationError($exception);
        });

        // 500: Generic fallback
        $exceptions->render(function (Throwable $exception, $request) {
            return ApiResponseService::handleUnexpectedError($exception);
        });
    })->create();

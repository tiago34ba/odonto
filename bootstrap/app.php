<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Middlewares de segurança para API
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\ApiSecurityMiddleware::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\DataAuditMiddleware::class,
            \App\Http\Middleware\SensitiveDataValidationMiddleware::class,
        ]);

        $middleware->alias([
            'api.permission' => \App\Http\Middleware\EnsureApiPermission::class,
            'patient.guard'  => \App\Http\Middleware\EnsurePatientAccess::class,
        ]);

        // Rate limiting global
        $middleware->throttleApi('api');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

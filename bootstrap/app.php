<?php

use App\Http\Middleware\RazorpayMiddleware;
use App\Http\Middleware\SabpaisaMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'sabpaisa' => SabpaisaMiddleware::class,
            'razorpay' => RazorpayMiddleware::class,
        ])->validateCsrfTokens(except: [
            'sabpaisa/*',
            'razorpay/*',
        ]);;
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

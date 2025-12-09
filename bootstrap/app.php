<?php

use App\Http\Middleware\PayoutMiddleware;
use App\Http\Middleware\PaytmMiddleware;
use App\Http\Middleware\PhonepeMiddleware;
use App\Http\Middleware\RazorpayMiddleware;
use App\Http\Middleware\SabpaisaMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'sabpaisa' => SabpaisaMiddleware::class,
            'razorpay' => RazorpayMiddleware::class,
            'phonepe' => PhonepeMiddleware::class,
            'paytm' => PaytmMiddleware::class,
            'payout' => PayoutMiddleware::class,
        ])->validateCsrfTokens(except: [
            'sabpaisa/*',
            'razorpay/*',
            'phonepe/*',
            'paytm/*',
        ]);;
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

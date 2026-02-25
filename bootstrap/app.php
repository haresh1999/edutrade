<?php

use App\Http\Middleware\{
    PayoutMiddleware,
    PaytmMiddleware,
    PhonepeMiddleware,
    RazorpayMiddleware,
    RazorpaySignatureMiddleware,
    SabpaisaMiddleware,
};

use Illuminate\Foundation\Application;

use Illuminate\Foundation\Configuration\{
    Exceptions,
    Middleware
};

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
            'razorpay.sign' => RazorpaySignatureMiddleware::class,
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

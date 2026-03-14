<?php

use App\Http\Middleware\{
    AuthMiddleware,
    SignatureMiddleware,
    TokenMiddleware,
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
            'auth' => AuthMiddleware::class,
            'signature' => SignatureMiddleware::class,
            'token' => TokenMiddleware::class,
        ])->validateCsrfTokens(except: []);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

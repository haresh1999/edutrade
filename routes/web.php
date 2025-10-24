<?php

use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\RazorpaySandboxController;
use App\Http\Controllers\SabpaisaController;
use App\Http\Controllers\SabpaisaSandboxController;
use Illuminate\Support\Facades\Route;

Route::prefix('sabpaisa')->group(function () {
    Route::post('request', [SabpaisaController::class, 'request'])->middleware('subpaisa');
    Route::post('status', [SabpaisaController::class, 'status'])->middleware('subpaisa');
    Route::any('callback', [SabpaisaController::class, 'callback']);
    Route::any('webhook', [SabpaisaController::class, 'webhook']);

    Route::prefix('sandbox')->group(function () {
        Route::post('request', [SabpaisaSandboxController::class, 'request'])->middleware('subpaisa');
        Route::post('status', [SabpaisaSandboxController::class, 'status'])->middleware('subpaisa');
        Route::any('callback', [SabpaisaSandboxController::class, 'callback']);
        Route::any('webhook', [SabpaisaController::class, 'webhook']);
    });
});

Route::prefix('razorpay')->group(function () {
    Route::post('request', [RazorpayController::class, 'request'])->middleware('razorpay');
    Route::post('status', [RazorpayController::class, 'status'])->middleware('razorpay');
    Route::any('callback', [RazorpayController::class, 'callback']);
    Route::any('webhook', [RazorpayController::class, 'webhook']);

    Route::prefix('sandbox')->group(function () {
        Route::post('request', [RazorpaySandboxController::class, 'request'])->middleware('razorpay');
        Route::post('status', [RazorpaySandboxController::class, 'status'])->middleware('razorpay');
        Route::any('callback', [RazorpaySandboxController::class, 'callback']);
        Route::any('webhook', [RazorpaySandboxController::class, 'webhook']);
    });
});

Route::view('sabpaisa-demo', 'sabpaisa_demo');
Route::view('razorpay-demo', 'razorpay_demo');
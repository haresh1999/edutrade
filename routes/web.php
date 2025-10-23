<?php

use App\Http\Controllers\SabpaisaController;
use App\Http\Controllers\SabpaisaSandboxController;
use Illuminate\Support\Facades\Route;

Route::prefix('sabpaisa')->middleware('subpaisa')->group(function () {
    Route::post('request', [SabpaisaController::class, 'request']);
    Route::post('status', [SabpaisaController::class, 'status']);
    Route::get('callback', [SabpaisaController::class, 'callback']);
    Route::any('webhook', [SabpaisaController::class, 'webhook']);

    Route::prefix('sandbox')->middleware('subpaisa')->group(function () {
        Route::post('request', [SabpaisaSandboxController::class, 'request']);
        Route::post('status', [SabpaisaSandboxController::class, 'status']);
        Route::get('callback', [SabpaisaSandboxController::class, 'callback']);
        Route::any('webhook', [SabpaisaController::class, 'webhook']);
    });
});


Route::view('payment-request', 'sabpaisa.test');

Route::get('success', function () {
    dd('Paid Successfully');
});

Route::get('failed', function () {
    dd('Failed Payment');
});

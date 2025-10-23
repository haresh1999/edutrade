<?php

use App\Http\Controllers\SabpaisaController;
use App\Http\Controllers\SabpaisaSandboxController;
use Illuminate\Support\Facades\Route;



Route::prefix('sabpaisa')->middleware('subpaisa')->group(function () {
    Route::get('request', [SabpaisaController::class, 'request']);
    Route::get('status', [SabpaisaController::class, 'status']);
    
    Route::prefix('sandbox')->middleware('subpaisa')->group(function () {
        Route::get('request', [SabpaisaSandboxController::class, 'request']);
        Route::get('status', [SabpaisaSandboxController::class, 'status']);
    });
});


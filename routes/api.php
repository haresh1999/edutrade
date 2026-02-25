<?php

use App\Http\Controllers\PayoutController;
use Illuminate\Support\Facades\Route;


Route::middleware('payout')->group(function () {
    Route::post('payout/request', [PayoutController::class, 'request']);
    Route::get('payout/status/{refid}', [PayoutController::class, 'status']);
});
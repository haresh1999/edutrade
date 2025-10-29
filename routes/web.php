<?php

use App\Http\Controllers\PhonepeController;
use App\Http\Controllers\PhonepeSandboxController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\RazorpaySandboxController;
use App\Http\Controllers\SabpaisaController;
use App\Http\Controllers\SabpaisaSandboxController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('sabpaisa')->group(function () {
    Route::post('request', [SabpaisaController::class, 'request'])->middleware('sabpaisa');
    Route::post('status', [SabpaisaController::class, 'status'])->middleware('sabpaisa');
    Route::any('callback', [SabpaisaController::class, 'callback']);
    Route::any('webhook', [SabpaisaController::class, 'webhook']);

    Route::prefix('sandbox')->group(function () {
        Route::post('request', [SabpaisaSandboxController::class, 'request'])->middleware('sabpaisa');
        Route::post('status', [SabpaisaSandboxController::class, 'status'])->middleware('sabpaisa');
        Route::any('callback', [SabpaisaSandboxController::class, 'callback']);
        Route::any('webhook', [SabpaisaController::class, 'webhook']);
    });
});

Route::prefix('razorpay')->group(function () {
    Route::post('token', [RazorpayController::class, 'token'])->middleware('razorpay');
    Route::post('request', [RazorpayController::class, 'request'])->middleware('razorpay');
    Route::post('status', [RazorpayController::class, 'status'])->middleware('razorpay');
    Route::any('callback', [RazorpayController::class, 'callback']);
    Route::any('webhook', [RazorpayController::class, 'webhook']);

    Route::prefix('sandbox')->group(function () {
        Route::post('token', [RazorpaySandboxController::class, 'token'])->middleware('razorpay');
        Route::post('request', [RazorpaySandboxController::class, 'request'])->middleware('razorpay');
        Route::post('status', [RazorpaySandboxController::class, 'status'])->middleware('razorpay');
        Route::any('callback', [RazorpaySandboxController::class, 'callback']);
        Route::any('webhook', [RazorpaySandboxController::class, 'webhook']);
    });
});

Route::prefix('phonepe')->group(function () {
    Route::post('create', [PhonepeController::class, 'create'])->middleware('phonepe');
    Route::post('request', [PhonepeController::class, 'request']);
    Route::post('status', [PhonepeController::class, 'status'])->middleware('phonepe');
    Route::any('callback', [PhonepeController::class, 'callback']);
    Route::any('webhook', [PhonepeController::class, 'webhook']);

    Route::prefix('sandbox')->group(function () {
        Route::post('create', [PhonepeSandboxController::class, 'create'])->middleware('phonepe');
        Route::post('request', [PhonepeSandboxController::class, 'request']);
        Route::post('status', [PhonepeSandboxController::class, 'status'])->middleware('phonepe');
        Route::any('callback', [PhonepeSandboxController::class, 'callback']);
        Route::any('webhook', [PhonepeSandboxController::class, 'webhook']);
    });
});

Route::view('sabpaisa-demo', 'sabpaisa_demo');
Route::view('razorpay-demo', 'razorpay_demo');
Route::view('phonepe-demo', 'phonepe_demo');

Route::get('payment-redirect', function (Request $request) {
    dd('dome');
});

Route::get('payment-callback', function (Request $request) {

    $data = json_encode($request->all());

    $myip = $request->ip();

    file_put_contents('sabpaisa_callback.txt', 'Webhook Received: ' . $data . ' From IP: ' . $myip);

    return response()->json([
        'status' => 'success'
    ]);
});

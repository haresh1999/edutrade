<?php

use App\Http\Controllers\PhonepeController;
use App\Http\Controllers\PhonepeSandboxController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\RazorpaySandboxController;
use App\Http\Controllers\SabpaisaController;
use App\Http\Controllers\SabpaisaSandboxController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\PaytmController;
use App\Http\Controllers\PaytmSandboxController;
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
    Route::post('token', [PhonepeController::class, 'token'])->middleware('phonepe');
    Route::post('create', [PhonepeController::class, 'create'])->middleware('phonepe');
    Route::post('request', [PhonepeController::class, 'request']);
    Route::post('status', [PhonepeController::class, 'status'])->middleware('phonepe');
    Route::any('callback', [PhonepeController::class, 'callback']);
    Route::any('webhook', [PhonepeController::class, 'webhook']);

    Route::prefix('sandbox')->group(function () {
        Route::post('token', [PhonepeSandboxController::class, 'token'])->middleware('phonepe');
        Route::post('create', [PhonepeSandboxController::class, 'create'])->middleware('phonepe');
        Route::post('request', [PhonepeSandboxController::class, 'request']);
        Route::post('status', [PhonepeSandboxController::class, 'status'])->middleware('phonepe');
        Route::any('callback', [PhonepeSandboxController::class, 'callback']);
        Route::any('webhook', [PhonepeSandboxController::class, 'webhook']);
    });
});

Route::prefix('paytm')->group(function () {
    Route::post('token', [PaytmController::class, 'token'])->middleware('paytm');
    Route::post('create', [PaytmController::class, 'create'])->middleware('paytm');
    Route::post('request', [PaytmController::class, 'request'])->middleware('paytm');
    Route::post('status', [PaytmController::class, 'status'])->middleware('paytm');
    Route::any('callback', [PaytmController::class, 'callback']);
    Route::any('webhook', [PaytmController::class, 'webhook']);

    Route::prefix('sandbox')->group(function () {
        Route::post('token', [PaytmSandboxController::class, 'token'])->middleware('paytm');
        Route::post('create', [PaytmSandboxController::class, 'create'])->middleware('paytm');
        Route::post('request', [PaytmSandboxController::class, 'request'])->middleware('paytm');
        Route::post('status', [PaytmSandboxController::class, 'status'])->middleware('paytm');
        Route::any('callback', [PaytmSandboxController::class, 'callback']);
        Route::any('webhook', [PaytmSandboxController::class, 'webhook']);
    });
});

Route::get('payout/request', [PayoutController::class, 'request']);

Route::view('sabpaisa-demo', 'sabpaisa_demo');
Route::view('razorpay-demo', 'razorpay_demo');
Route::view('phonepe-demo', 'phonepe_demo');
Route::view('paytm-demo', 'paytm_demo');

// Route::get('payment-redirect', function (Request $request) {
//     dd('dome');
// });

// Route::post('payment-callback', function (Request $request) {

//     $data = json_encode($request->all());

//     $publicIp = file_get_contents('https://api.ipify.org');

//     file_put_contents('sabpaisa_callback.txt', 'Webhook Received: ' . $data . ' From IP: ' . $publicIp);

//     return response()->json([
//         'status' => 'success'
//     ]);
// });

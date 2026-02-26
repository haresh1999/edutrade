<?php

use App\Http\Controllers\{
    PhonepeController,
    PhonepeSandboxController,
    RazorpayController,
    RazorpaySandboxController,
    SabpaisaController,
    SabpaisaSandboxController,
    PaytmController,
    PaytmSandboxController,
};

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
    Route::post('get/order-id', [RazorpayController::class, 'getOrderId']);
    Route::post('request', [RazorpayController::class, 'request'])->middleware(['throttle:600,10', 'razorpay', 'razorpay.sign']);
    Route::get('checkout/order-pay/{id}', [RazorpayController::class, 'orderPay']);
    Route::post('status', [RazorpayController::class, 'status'])->middleware(['throttle:600,10', 'razorpay']);
    Route::post('callback', [RazorpayController::class, 'callback'])->middleware(['throttle:60,1']);
    Route::post('webhook', [RazorpayController::class, 'webhook'])->middleware(['throttle:30,1', 'razorpay.webhook']);
});

Route::prefix('razorpay/sandbox')->group(function () {
    Route::post('token', [RazorpaySandboxController::class, 'token'])->middleware('razorpay');
    Route::post('get/order-id', [RazorpaySandboxController::class, 'getOrderId']);
    Route::post('request', [RazorpaySandboxController::class, 'request'])->middleware(['throttle:600,10', 'razorpay', 'razorpay.sign']);
    Route::get('checkout/order-pay/{id}', [RazorpaySandboxController::class, 'orderPay']);
    Route::post('status', [RazorpaySandboxController::class, 'status'])->middleware(['throttle:600,10', 'razorpay']);
    Route::post('callback', [RazorpaySandboxController::class, 'callback'])->middleware(['throttle:60,1']);
    Route::post('webhook', [RazorpaySandboxController::class, 'webhook'])->middleware(['throttle:30,1', 'razorpay.webhook']);
});

// Route::prefix('razorpay')->group(function () {
//     Route::post('token', [RazorpayController::class, 'token'])->middleware('razorpay');
//     Route::post('request', [RazorpayController::class, 'request'])->middleware(['throttle:600,10', 'razorpay', 'razorpay.sign']);
//     Route::post('status', [RazorpayController::class, 'status'])->middleware(['throttle:600,10', 'razorpay', 'razorpay.sign']);
//     Route::match(['get', 'post'], 'callback', [RazorpayController::class, 'callback']);
//     Route::match(['get', 'post'], 'webhook', [RazorpayController::class, 'webhook']);

//     Route::prefix('sandbox')->group(function () {
//         Route::post('token', [RazorpaySandboxController::class, 'token'])->middleware('razorpay');
//         Route::post('request', [RazorpaySandboxController::class, 'request'])->middleware(['throttle:600,10', 'razorpay', 'razorpay.sign']);
//         Route::post('status', [RazorpaySandboxController::class, 'status'])->middleware(['throttle:600,10', 'razorpay', 'razorpay.sign']);
//         Route::match(['get', 'post'], 'callback', [RazorpaySandboxController::class, 'callback']);
//         Route::match(['get', 'post'], 'webhook', [RazorpaySandboxController::class, 'webhook']);
//     });
// });

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

// Route::view('sabpaisa-demo', 'sabpaisa_demo');
Route::view('razorpay-demo', 'razorpay_demo');
// Route::view('phonepe-demo', 'phonepe_demo');
// Route::view('paytm-demo', 'paytm_demo');

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


// Route::get('generate-sign', function () {

//     $secret = config("services.razorpay.production.key_sign");

//     $payload = [
//         "amount" => "1",
//         "order_id" => "00005648",
//         "payer_email" => "hareshc1999@gmail.com",
//         "payer_mobile" => "9106029220",
//         "payer_name" => "Haresh",
//         "refresh_token" => "4008d0e0-90ce-4c6a-9118-6698e10d0396-1",
//     ];

//     ksort($payload);

//     $payloadQueryString = http_build_query($payload);

//     $calculatedSignature = hash_hmac('sha256', $payloadQueryString, $secret);

//     dd($calculatedSignature);
// });


// $signature = $request->header('X-Provider-Signature');

// $data = $request->all();

// ksort($data);

// $payload = http_build_query($data);

// $expected = hash_hmac('sha256', $payload, $secret);

// hash_equals($expected, $signature);
<?php

namespace App\Http\Controllers;

use App\Models\RazorpaySandboxOrder;
use App\Models\RazorpaySandboxToken;
use App\Models\RazorpayUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RazorpaySandboxController extends Controller
{
    public function token()
    {
        $userId = config('services.razorpay.user.id');

        $token = str()->uuid() . '-' . $userId;

        RazorpaySandboxToken::create([
            'user_id' => $userId,
            'token' => $token
        ]);

        return response()->json([
            'refresh_token' => $token
        ]);
    }

    public function getOrderId(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:razorpay_sandbox_orders,id']
        ]);

        $tnx = RazorpaySandboxOrder::findOrFail($request->id);

        $keyId = setting('key_id');
        $keySecret = setting('key_secret');

        $amount   = (int) ($tnx->amount * 100);
        $currency = "INR";

        $data = [
            "amount" => $amount,
            "currency" => $currency,
            "payment_capture" => 1,
            "notes" => [
                "woocommerce_order_id" => $tnx->id,
                "woocommerce_order_number" => $tnx->id
            ],
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.razorpay.com/v1/orders",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $keyId . ":" . $keySecret,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            curl_close($ch);
            return response()->json([
                'error' => true,
                'message' => curl_error($ch)
            ], 500);
        }

        curl_close($ch);

        $result = json_decode($response, true);

        $tnx->update(['request_response' => json_encode($result)]);

        if ($httpCode === 200 && isset($result['id'])) {
            return  response()->json($result['id']);
        } else {

            return response()->json([
                'error' => true,
                'razorpay_response' => $result
            ], 400);
        }
    }

    public function request(Request $request)
    {
        $userId = config('services.razorpay.user.id');

        $validator = Validator::make($request->all(), [
            'order_id' => [
                'required',
                'string',
                Rule::unique('razorpay_sandbox_orders', 'order_id')->where('user_id', $userId)
            ],
            'amount' => ['required', 'numeric', 'min:1'],
            'payer_name' => ['required', 'string', 'max:255'],
            'payer_email' => ['required', 'email', 'max:255'],
            'payer_mobile' => ['required', 'digits_between:9,11'],
            'callback_url' => ['required', 'url'],
            'redirect_url' => ['required', 'url'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $input = $validator->validated();

        $tnx = RazorpaySandboxOrder::create([
            'user_id' => $userId,
            'order_id' => $input['order_id'],
            'amount' => $input['amount'],
            'payer_name' => $input['payer_name'],
            'payer_email' => $input['payer_email'],
            'payer_mobile' => $input['payer_mobile'],
            'request_response' => json_encode([]),
        ]);

        RazorpayUser::where('id', $userId)->update([
            'callback_url' => $input['callback_url'],
            'redirect_url' => $input['redirect_url']
        ]);

        $string = str()->random(13);

        $url = url('razorpay/sandbox/checkout/order-pay/' . $tnx->id . '?key=wc_order_' . $string);

        return redirect()->to($url);
    }

    public function orderPay($tid)
    {
        $transaction = RazorpaySandboxOrder::findOrFail($tid);

        $input['callback_url'] = env('RAZORPAY_SANDBOX_REDIRECT_URL') . "?tnx_id={$transaction->id}";

        $input['order_id_url'] = url('razorpay/sandbox/get/order-id');

        $input['order_id'] = $transaction->order_id;
        $input['amount'] = $transaction->amount;
        $input['payer_name'] = $transaction->payer_name;
        $input['payer_email'] = $transaction->payer_email;
        $input['payer_mobile'] = $transaction->payer_mobile;
        $input['tnx_id'] = $transaction->id;

        return view('razorpay.request', compact('input'));
    }

    public function status(Request $request)
    {
        $userId = config('services.razorpay.user.id');

        $validator = Validator::make($request->all(), [
            'tnx_id' => ['required', 'string', Rule::exists('razorpay_sandbox_orders', 'tnx_id')->where('user_id', $userId)],
        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $input = $validator->validated();

        $order = RazorpaySandboxOrder::where('user_id', $userId)
            ->where('tnx_id', $input['tnx_id'])
            ->first();

        return response()->json([
            'order_id' => $order->order_id,
            'tnx_id' => $order->tnx_id,
            'amount' => $order->amount,
            'status' => $order->status,
            'payer_name' => $order->payer_name,
            'payer_email' => $order->payer_email,
            'payer_mobile' => $order->payer_mobile,
        ]);
    }

    public function webhook($url, $data)
    {
        ksort($data);

        $payloadQueryString = http_build_query($data);

        $secret = config('services.razorpay.sandbox.key_sign');

        $calculatedSignature = hash_hmac('sha256', $payloadQueryString, $secret);

        return Http::withHeaders([
            'X-Provider-Signature' => $calculatedSignature,
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])->post($url, $data);
    }

    public function callback(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => ['required'],
            'razorpay_signature' => ['required'],
            'razorpay_order_id' => ['required'],
            'tnx_id' => ['required', 'integer', 'exists:razorpay_sandbox_orders,id']
        ]);

        $keyId = setting('key_id');
        $keySecret = setting('key_secret');

        $paymentId = $request->razorpay_payment_id;

        $order = RazorpaySandboxOrder::findOrFail($request->tnx_id);

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.razorpay.com/v1/payments/" . $paymentId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $keyId . ":" . $keySecret,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode === 200) {

            $status = $result['status'];

            if ($status === 'captured') {

                $order->update([
                    'refund_response' => json_encode($result),
                    'status' => 'completed',
                ]);
            } else {
                $order->update([
                    'payment_response' => json_encode($result),
                    'status' => 'failed'
                ]);
            }
        } else {
            $order->update([
                'payment_response' => json_encode($result),
                'status' => 'failed'
            ]);
        }

        $tnx = RazorpaySandboxOrder::findOrFail($request->tnx_id);

        $sendData = [
            'order_id' => $tnx->order_id,
            'tnx_id' => $tnx->tnx_id,
            'amount' => $tnx->amount,
            'status' => $tnx->status
        ];

        if (! is_null($tnx->user->callback_url)) {

            $this->webhook($tnx->user->callback_url, $sendData);
        }

        $status = $tnx->status;

        return redirect()->to($tnx->user->redirect_url . '?status=' . $status);
    }
}

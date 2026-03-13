<?php

namespace App\Http\Controllers;

use App\Models\RazorpayCallbackUrl;
use App\Models\RazorpayOrder;
use App\Models\RazorpayToken;
use App\Models\RazorpayUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RazorpayController extends Controller
{
    public function getOrderId(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:razorpay_orders,id']
        ]);

        $tnx = RazorpayOrder::findOrFail($request->id);

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

    public function orderPay($tid)
    {
        $transaction = RazorpayOrder::findOrFail($tid);

        $input['callback_url'] = env('RAZORPAY_REDIRECT_URL') . "?tnx_id={$transaction->id}";

        $input['order_id_url'] = url('razorpay/get/order-id');

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
            'tnx_id' => ['required', 'string', Rule::exists('razorpay_orders', 'tnx_id')->where('user_id', $userId)],
        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $input = $validator->validated();

        $order = RazorpayOrder::where('user_id', $userId)
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

        $secret = config('services.razorpay.production.key_sign');

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
            'tnx_id' => ['required', 'integer', 'exists:razorpay_orders,id']
        ]);

        $keyId = setting('key_id');
        $keySecret = setting('key_secret');

        $paymentId = $request->razorpay_payment_id;

        $order = RazorpayOrder::findOrFail($request->tnx_id);

        $urls = RazorpayCallbackUrl::where([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'tnx_id' => $order->tnx_id
        ])->first();

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
                    'request_response' => json_encode($result),
                    'status' => 'completed',
                ]);
            } else {
                $order->update([
                    'request_response' => json_encode($result),
                    'status' => 'failed'
                ]);
            }
        } else {
            $order->update([
                'request_response' => json_encode($result),
                'status' => 'failed'
            ]);
        }

        $tnx = RazorpayOrder::findOrFail($request->tnx_id);

        $sendData = [
            'order_id' => $tnx->order_id,
            'tnx_id' => $tnx->tnx_id,
            'amount' => $tnx->amount,
            'status' => $tnx->status
        ];

        $callback_url = $urls->callback_url;

        $this->webhook($callback_url, $sendData);

        $status = $tnx->status;

        $redirect_url = $urls->redirect_url;

        return redirect()->to($redirect_url . '?status=' . $status);
    }

    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'in:a715da0a-db2a-4f15-8df8-56fa7ff5a2f9'],
        ]);

        if ($validator->fails()) {

            return redirect()->to('https://edutrade.in');
        }

        return view('razorpay.verify_payment');
    }

    public function paymentUpdate(Request $request)
    {
        $input = $request->validate([
            'order_id' => ['required', Rule::exists('razorpay_orders', 'order_id')->where('status', 'pending')],
            'status' => ['required', 'in:completed,failed,refunded,processing,pending'],
            'password' => ['required', 'in:36351231-e783-4462-ac13-2c1f2d5fca25']
        ]);

        $order = RazorpayOrder::where('order_id', $input['order_id'])->first();

        $order->update(['status' => $input['status']]);

        $callback = RazorpayCallbackUrl::where('order_id', $order->id)
            ->where('user_id', $order->user_id)
            ->where('tnx_id', $order->tnx_id)
            ->first();

        $callback_url = $callback->callback_url;

        $sendData = [
            'order_id' => $order->order_id,
            'tnx_id' => $order->tnx_id,
            'amount' => $order->amount,
            'status' => $input['status']
        ];

        $this->webhook($callback_url, $sendData);

        return redirect()
            ->back()
            ->with('success', 'Payment updated successfully');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RazorpayController extends Controller
{
    public function getOrderId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference_id' => ['required', Rule::exists('transactions')->where('status', 'pending')->where('env', 'production')]
        ]);

        if ($validator->fails()) {

            return response()->json('error', $validator->errors()->first());
        }

        $input = $validator->validated();

        $transaction = Transaction::where('reference_id', $input['reference_id'])->first();

        $keyId = setting('razorpay', 'key_id');
        $keySecret = setting('razorpay', 'key_secret');

        $amount   = (int) ($transaction->amount * 100);
        $currency = "INR";

        $data = [
            "amount" => $amount,
            "currency" => $currency,
            "payment_capture" => 1,
            "notes" => [
                "woocommerce_order_id" => $transaction->id,
                "woocommerce_order_number" => $transaction->id
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

        $transaction->update(['request_response' => json_encode($result)]);

        if ($httpCode === 200 && isset($result['id'])) {
            return  response()->json($result['id']);
        } else {

            return response()->json([
                'error' => true,
                'razorpay_response' => $result
            ], 400);
        }
    }

    public function orderPay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference_id' => ['required', Rule::exists('transactions')->where('status', 'pending')->where('env', 'production')]
        ]);

        if ($validator->fails()) {

            return redirect()->to(env('APP_URL'));
        }

        $input = $validator->validated();

        $transaction = Transaction::where('reference_id', $input['reference_id'])->first();

        $input['callback_url'] = env('RAZORPAY_REDIRECT_URL') . "?reference_id={$transaction->reference_id}";

        $input['order_id_url'] = url('razorpay/get/order-id');

        $input['order_id'] = $transaction->order_id;
        $input['amount'] = $transaction->amount;
        $input['payer_name'] = $transaction->payer_name;
        $input['payer_email'] = $transaction->payer_email;
        $input['payer_mobile'] = $transaction->payer_mobile;
        $input['reference_id'] = $transaction->reference_id;
        $input['id'] = $transaction->id;

        return view('razorpay.request', compact('input'));
    }

    public function callback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'razorpay_payment_id' => ['required'],
            'razorpay_signature' => ['required'],
            'razorpay_order_id' => ['required'],
            'reference_id' => ['required', Rule::exists('transactions')->where('status', 'pending')->where('env', 'production')]
        ]);

        if ($validator->fails()) {
            return redirect()->to(env('APP_URL'));
        }

        $input = $validator->validated();

        $keyId = setting('razorpay', 'key_id');
        $keySecret = setting('razorpay', 'key_secret');

        $paymentId = $request->razorpay_payment_id;

        $transaction = Transaction::where('reference_id', $input['reference_id'])->first();

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

                $transaction->update([
                    'request_response' => json_encode($result),
                    'status' => 'completed',
                ]);
            } else {
                $transaction->update([
                    'request_response' => json_encode($result),
                    'status' => 'failed'
                ]);
            }
        } else {
            $transaction->update([
                'request_response' => json_encode($result),
                'status' => 'failed'
            ]);
        }

        return redirect()->route('redirect');
    }
}

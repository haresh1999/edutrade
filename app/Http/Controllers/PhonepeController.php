<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PhonepeController extends Controller
{
    private function getAccessToken()
    {
        $url = 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';

        $fields = [
            'client_id' => 'SU2509231940115728161187',
            'client_version' => 1,
            'client_secret' => 'ef6c3f40-db7b-4839-b97d-cc114df6d895',
            'grant_type' => 'client_credentials'
        ];

        $headers = [
            'Content-Type: application/x-www-form-urlencoded'
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields), // encodes as form data
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($ch);

        curl_close($ch);

        return json_decode($response)->access_token;
    }

    public function request(Request $request)
    {
        $userId = config('services.phonepe.user.id');

        $input = $request->validate([
            'order_id' => ['required', 'string', Rule::unique('phonepe_orders', 'order_id')->where('user_id', $userId)],
            'amount' => ['required', 'numeric', 'min:1'],
            'payer_name' => ['required', 'string', 'max:255'],
            'payer_email' => ['required', 'email', 'max:255'],
            'payer_mobile' => ['required', 'digits_between:9,11'],
        ]);

        $url = "https://api.razorpay.com/v1/payment_links";
        $amount = ($input['amount'] * 100);
        $callbackUrl = env('PHONEPE_CALLBACK_URL') . "?order_id={$input['order_id']}";

        $data = [
            "amount" => $amount,
            "currency" => "INR",
            "accept_partial" => false,
            "description" => "Test payment link via cURL",
            "customer" => [
                "name" => $input['payer_name'],
                "email" => $input['payer_email'],
                "contact" => $input['payer_mobile']
            ],
            "notify" => [
                "sms" => true,
                "email" => true
            ],
            "reminder_enable" => true,
            "callback_url" => $callbackUrl,
            "callback_method" => "get"
        ];

        $key_id = setting('key_id');
        $key_secret = setting('key_secret');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $key_id . ":" . $key_secret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {

            return response()->json([
                'status' => 'error',
                'message' => curl_error($ch)
            ], 401);
        }

        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['short_url'])) {

            RazorpayOrder::create([
                'user_id' => $userId,
                'order_id' => $input['order_id'],
                'amount' => $input['amount'],
                'payer_name' => $input['payer_name'],
                'payer_email' => $input['payer_email'],
                'payer_mobile' => $input['payer_mobile'],
                'request_response' => $response,
            ]);

            return redirect()->to($result['short_url']);
        } else {

            RazorpayOrder::create([
                'user_id' => $userId,
                'order_id' => $input['order_id'],
                'amount' => $input['amount'],
                'payer_name' => $input['payer_name'],
                'payer_email' => $input['payer_email'],
                'payer_mobile' => $input['payer_mobile'],
                'request_response' => $response,
                'status' => 'failed',
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create payment link',
                'details' => $result
            ], 401);
        }
    }

    public function status(Request $request)
    {
        $userId = config('services.phonepe.user.id');

        $validator = Validator::make($request->all(), [
            'tnx_id' => ['required', 'string', Rule::exists('phonepe_orders', 'tnx_id')->where('user_id', $userId)],
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

    public function callback(Request $request)
    {
        if ($request->has('order_id') && $request->has('razorpay_payment_link_id')) {

            $payment_link_id = $request->razorpay_payment_link_id ?? '';
            $payment_link_ref = $request->razorpay_payment_link_reference_id ?? '';
            $payment_id = $request->razorpay_payment_id ?? '';
            $payment_status = $request->razorpay_payment_link_status ?? '';
            $signature = $request->razorpay_signature ?? '';

            RazorpayOrder::where('order_id', $request->order_id)->update([
                'status' => $payment_status == 'paid' ? 'paid' : 'failed',
                'request_response' => json_encode($request->all()),
            ]);

            $order = RazorpayOrder::with('user')->where('order_id', $request->order_id)->first();

            $sendData = json_encode([
                'order_id' => $order->order_id,
                'tnx_id' => $order->tnx_id,
                'amount' => $order->amount,
                'status' => $order->status
            ]);

            $backUrl = "{$order->user->callback_url}?response={$sendData}";

            return redirect()->to($backUrl);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong!, Unable to handle response',
        ], 401);
    }

    public function webhook()
    {
        //
    }
}

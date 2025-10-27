<?php

namespace App\Http\Controllers;

use App\Models\RazorpayOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RazorpayController extends Controller
{
    public function request(Request $request)
    {
        $userId = config('services.razorpay.user.id');

        $input = $request->validate([
            'order_id' => ['required', 'string', Rule::unique('razorpay_orders', 'order_id')->where('user_id', $userId)],
            'amount' => ['required', 'numeric', 'min:1'],
            'payer_name' => ['required', 'string', 'max:255'],
            'payer_email' => ['required', 'email', 'max:255'],
            'payer_mobile' => ['required', 'digits_between:9,11'],
        ]);

        $url = "https://api.razorpay.com/v1/payment_links";
        $amount = ($input['amount'] * 100);
        $callbackUrl = env('RAZORPAY_REDIRECT_URL') . "?order_id={$input['order_id']}";

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

    public function callback(Request $request)
    {
        if ($request->has('order_id') && $request->has('razorpay_payment_link_id')) {

            $payment_link_id = $request->razorpay_payment_link_id ?? '';
            $payment_link_ref = $request->razorpay_payment_link_reference_id ?? '';
            $payment_id = $request->razorpay_payment_id ?? '';
            $payment_status = $request->razorpay_payment_link_status ?? '';
            $signature = $request->razorpay_signature ?? '';

            RazorpayOrder::where('order_id', $request->order_id)->update([
                'status' => $payment_status == 'paid' ? 'completed' : 'failed',
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

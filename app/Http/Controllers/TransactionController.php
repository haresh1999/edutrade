<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function getToken()
    {
        $userId = config('services.user.id');

        $token = str()->uuid()->toString() . '-' . $userId;

        Token::create([
            'user_id' => $userId,
            'token' => $token,
            'ip_address' => request()->ip()
        ]);

        return response()->json([
            'refresh_token' => $token
        ]);
    }

    public function request(Request $request)
    {
        $user = config('services.user');
        $env = config('services.env');

        $validator = Validator::make($request->all(), [
            'order_id' => [
                'required',
                Rule::unique('transactions', 'order_id')->where(function ($query) use ($user, $env) {
                    return $query->where('user_id', $user['id'])
                        ->where('env', $env);
                }),
            ],
            'amount' => ['required', 'numeric', 'min:1'],
            'payer_name' => ['required', 'string', 'max:255'],
            'payer_email' => ['required', 'email', 'max:255'],
            'payer_mobile' => ['required', 'digits:10'],
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

        $gateway = $user['default_gateway'] ?? array_rand(['razorpay']);

        $tnx = Transaction::create([
            'user_id' => $user['id'],
            'order_id' => $input['order_id'],
            'amount' => $input['amount'],
            'payer_name' => $input['payer_name'],
            'payer_email' => $input['payer_email'],
            'payer_mobile' => $input['payer_mobile'],
            'gateway' => $gateway,
            'callback_url' => $input['callback_url'],
            'redirect_url' => $input['redirect_url']
        ]);

        $string = str()->random(13);

        if ($env == 'sandbox') {

            $url = "{$gateway}/{$env}/order-pay/{$tnx->id}?key=wc_order_{$string}";
        } else {

            $url = "{$gateway}/order-pay/{$tnx->id}?key=wc_order_{$string}";
        }

        return redirect()->to($url);
    }

    public function webhook($url, $secret, $data)
    {
        ksort($data);

        $payloadQueryString = http_build_query($data);

        $calculatedSignature = hash_hmac('sha256', $payloadQueryString, $secret);

        return Http::withHeaders([
            'X-Provider-Signature' => $calculatedSignature,
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])->post($url, $data);
    }

    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'in:a715da0a-db2a-4f15-8df8-56fa7ff5a2f9'],
        ]);

        if ($validator->fails()) {

            return redirect()->to('https://apexonline.in');
        }

        return view('verify_payment');
    }

    public function paymentUpdate(Request $request)
    {
        $user = User::firstWhere('email', $request->email);

        if (!$user || !Hash::check($request->password, $user->password)) {

            return redirect()->back()->with('message', 'Invalid credentials');
        }

        $input = $request->validate([
            'order_id' => [
                'required',
                Rule::exists('transactions', 'order_id')->where(function ($query) use ($user) {
                    $query->where('env', $user->env)->where('status', 'pending');
                }),
            ],
            'status' => ['required', 'in:completed,failed,refunded,processing,pending'],
            'password' => ['required']
        ]);

        $transaction = Transaction::where('order_id', $input['order_id'])
            ->where('user_id', $user->id)
            ->where('env', $user->env)
            ->first();

        if (! $transaction) {

            return redirect()->back()->with('message', 'Order not found!');
        }

        $transaction->update(['status' => $input['status']]);

        $callback_url = $transaction->callback_url;

        $sendData = [
            'order_id' => $transaction->order_id,
            'tnx_id' => $transaction->id,
            'amount' => $transaction->amount,
            'status' => $input['status']
        ];

        $this->webhook($callback_url, $user->callback_secret, $sendData);

        return redirect()
            ->back()
            ->with('success', 'Payment updated successfully');
    }
}

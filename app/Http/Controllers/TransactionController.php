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

        if ($env == 'sandbox') {

            $url = "{$gateway}/{$env}/order-pay?reference_id={$tnx->uuid}&key=wc_order_{$tnx->id}";
        } else {

            $url = "{$gateway}/order-pay?reference_id={$tnx->uuid}&key=wc_order_{$tnx->id}";
        }

        return redirect()->to($url);
    }

    protected function webhook($url, $secret, $data)
    {
        ksort($data);

        $payloadQueryString = http_build_query($data);

        $calculatedSignature = hash_hmac('sha256', $payloadQueryString, $secret);

        return Http::withHeaders([
            'X-Provider-Signature' => $calculatedSignature,
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])->post($url, $data);
    }

    public function status(Request $request)
    {
        $userId = config('services.phonepe.user.id');

        $env = config('services.env');

        $validator = Validator::make($request->all(), [
            'order_id' => ['required', 'string',   Rule::unique('transactions', 'order_id')->where(function ($query) use ($userId, $env) {
                return $query->where('user_id', $userId)->where('env', $env);
            }),],
        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $input = $validator->validated();

        $transaction = Transaction::where('user_id', $userId)
            ->where('order_id', $input['order_id'])
            ->where('env', $env)
            ->first();

        return response()->json([
            'transaction_id' => $transaction->id,
            'order_id' => $transaction->order_id,
            'reference_id' => $transaction->reference_id,
            'amount' => $transaction->amount,
            'refund_amount' => $transaction->refund_amount,
            'status' => $transaction->status,
            'payer_name' => $transaction->payer_name,
            'payer_email' => $transaction->payer_email,
            'payer_mobile' => $transaction->payer_mobile,
            'redirect_url' => $transaction->redirect_url,
            'callback_url' => $transaction->callback_url,
        ]);
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

    public function redirect(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference_id' => ['required', Rule::exists('transactions', 'reference_id')]
        ]);

        if ($validator->fails()) {

            return redirect()->to(env('APP_URL'));
        }

        $input = $validator->validated();

        $transaction = Transaction::where('reference_id', $input['reference_id'])->first();

        $callback_secret = User::where('id', $transaction->user_id)->value();

        $sendData = [
            'transaction_id' => $transaction->id,
            'order_id' => $transaction->order_id,
            'reference_id' => $transaction->reference_id,
            'amount' => $transaction->amount,
            'refund_amount' => $transaction->refund_amount,
            'status' => $transaction->status,
            'payer_name' => $transaction->payer_name,
            'payer_email' => $transaction->payer_email,
            'payer_mobile' => $transaction->payer_mobile,
            'redirect_url' => $transaction->redirect_url,
            'callback_url' => $transaction->callback_url,
        ];

        $callback_url = $transaction->callback_url;

        $this->webhook($callback_url, $callback_secret, $sendData);

        return redirect()->to($transaction->redirect_url . '?status=' . $transaction->status);
    }
}

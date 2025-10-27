<?php

namespace App\Http\Controllers;

use App\Classes\SabpaisaAuth;
use App\Models\SabpaisaOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SabpaisaController extends Controller
{
    public function request(Request $request, SabpaisaAuth $sabpaisaAuth)
    {
        $userId = config('services.sabpaisa.user.id');

        $input = $request->validate([
            'order_id' => ['required', 'string', 'max:255', Rule::unique('sabpaisa_orders', 'order_id')->where('user_id', $userId)],
            'amount' => ['required', 'numeric', 'min:1'],
            'payer_name' => ['required', 'string', 'max:255'],
            'payer_email' => ['required', 'email', 'max:255'],
            'payer_mobile' => ['required', 'digits_between:9,11'],
        ]);

        $input['currency'] = 'INR';
        $input['mcc'] = 5137;
        $input['channel_id'] = 'W';
        $input['callback_url'] = env('SABPAISA_CALLBACK_URL');
        $input['class'] = 'VIII';
        $input['roll'] = '1008';
        $input['url'] = 'https://securepay.sabpaisa.in/SabPaisa/sabPaisaInit?v=1';
        $input['user_id'] = $userId;

        $clientCode = setting('client_code');
        $username = setting('username');
        $password = setting('password');
        $authKey = setting('auth_key');
        $authIV = setting('auth_iv');

        $encData = "?clientCode=" . $clientCode . "&transUserName=" . $username . "&transUserPassword=" . $password .
            "&payerName=" . $input['payer_name'] . "&payerMobile=" . $input['payer_mobile'] . "&payerEmail=" . $input['payer_email'] . "&clientTxnId=" . $input['order_id'] . "&amount=" . $input['amount'] . "&amountType=" . $input['currency'] . "&mcc=" . $input['mcc'] . "&channelId=" . $input['channel_id'] .
            "&callbackUrl=" . $input['callback_url'] . "&udf1=" . $input['class'] . "&udf2=" . $input['roll'];

        $input['enc_data'] = $sabpaisaAuth->encrypt($authKey, $authIV, $encData);

        SabpaisaOrder::create($input);

        return view('sabpaisa.request', compact('input', 'clientCode'));
    }

    public function status(Request $request)
    {
        $userId = config('services.sabpaisa.user.id');

        $validator = Validator::make($request->all(), [
            'tnx_id' => ['required', 'string', Rule::exists('sabpaisa_orders', 'tnx_id')->where('user_id', $userId)],
        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $input = $validator->validated();

        $order = SabpaisaOrder::where('user_id', $userId)
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

    protected function clientCallback($url, $data)
    {
        return Http::post($url, $data);
    }

    public function callback(Request $request, SabpaisaAuth $sabpaisaAuth)
    {
        $input = $request->all();

        if (isset($input['encResponse'])) {

            $authKey = setting('auth_key');
            $authIV = setting('auth_iv');

            $decText = $sabpaisaAuth->decrypt($authKey, $authIV, $input['encResponse']);

            $token = strtok($decText, "&");

            $i = 0;

            while ($token !== false) {
                $i = $i + 1;
                $token1 = strchr($token, "=");
                $token = strtok("&");
                $fstr = ltrim($token1, "=");

                if ($i == 4) {
                    $clientTxnId = $fstr;
                }

                if ($i == 12) {
                    $status = $fstr;
                }
            }

            SabpaisaOrder::where('order_id', $clientTxnId)->update([
                'status' => in_array(strtolower($status), ['success', 'paid']) ? 'completed' : 'failed',
                'request_response' => $decText,
            ]);

            $order = SabpaisaOrder::with('user')->where('order_id', $clientTxnId)->first();

            $sendData = [
                'order_id' => $order->order_id,
                'tnx_id' => $order->tnx_id,
                'amount' => $order->amount,
                'status' => $order->status,
                'payer_name' => $order->payer_name,
                'payer_email' => $order->payer_email,
                'payer_mobile' => $order->payer_mobile,
            ];

            $redirectUrl = $order->user->redirect_url;

            $callbackUrl = $order->user->callback_url;

            $this->clientCallback($callbackUrl, $sendData);

            return redirect()->to($redirectUrl);
        }

        return response()->json([
            'error' => 'Payment failed or cancelled',
            'message' => 'Unable to process payment',
        ], 402);
    }

    public function webhook(Request $request, SabpaisaAuth $sabpaisaAuth)
    {
        $data = $request->input('encData');

        $authKey = setting('auth_key');
        $authIV = setting('auth_iv');

        $decText = $sabpaisaAuth->decrypt($authKey, $authIV, $data);

        $token = strtok($decText, "&");

        $i = 0;

        while ($token !== false) {
            $i = $i + 1;
            $token1 = strchr($token, "=");
            $token = strtok("&");
            $fstr = ltrim($token1, "=");

            if ($i == 4) {
                $clientTxnId = $fstr;
            }

            if ($i == 12) {
                $status = $fstr;
            }
        }

        SabpaisaOrder::where('order_id', $clientTxnId)->update([
            'status' => in_array(strtolower($status), ['success', 'paid']) ? 'completed' : 'failed',
            'request_response' => $decText,
        ]);

        $order = SabpaisaOrder::with('user')->where('order_id', $clientTxnId)->first();

        if ($order->user->notify_url != null) {

            Http::post($order->user->notify_url, [
                'order_id' => $order->order_id,
                'amount' => $order->amount,
                'tnx_id' => $order->tnx_id,
                'status' => in_array(strtolower($status), ['success', 'paid']) ? 'completed' : 'failed'
            ]);
        }

        return response('OK', 200);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\SabpaisaAuthSandbox;
use App\Http\Requests\SabpaisaSandboxRequest;
use App\Models\SabpaisaSandboxOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SabpaisaSandboxController extends Controller
{
    public function request(SabpaisaSandboxRequest $request, SabpaisaAuthSandbox $sabpaisaAuth)
    {
        $input = $request->validated();

        $input['currency'] = 'INR';
        $input['mcc'] = 5137;
        $input['channel_id'] = 'W';
        $input['callback_url'] = env('SABPAISA_SANDBOX_CALLBACK_URL');
        $input['class'] = 'VIII';
        $input['roll'] = '1008';
        $input['url'] = sabpaisaSandbox('url');
        $input['user_id'] = config('services.sabpaisa.user.id');

        $clientCode = sabpaisaSandbox('client_code');
        $username = sabpaisaSandbox('username');
        $password = sabpaisaSandbox('password');
        $authKey = sabpaisaSandbox('auth_key');
        $authIV = sabpaisaSandbox('auth_iv');

        $encData = "?clientCode=" . $clientCode . "&transUserName=" . $username . "&transUserPassword=" . $password .
            "&payerName=" . $input['payer_name'] . "&payerMobile=" . $input['payer_mobile'] . "&payerEmail=" . $input['payer_email'] . "&clientTxnId=" . $input['order_id'] . "&amount=" . $input['amount'] . "&amountType=" . $input['currency'] . "&mcc=" . $input['mcc'] . "&channelId=" . $input['channel_id'] .
            "&callbackUrl=" . $input['callback_url'] . "&udf1=" . $input['class'] . "&udf2=" . $input['roll'];

        $input['enc_data'] = $sabpaisaAuth->encrypt($authKey, $authIV, $encData);

        SabpaisaSandboxOrder::create($input);

        return view('sabpaisa.request', compact('input', 'clientCode'));
    }

    public function callback(Request $request, SabpaisaAuthSandbox $sabpaisaAuth)
    {
        $input = $request->all();

        if (isset($input['encResponse'])) {

            $authKey = sabpaisaSandbox('auth_key');
            $authIV = sabpaisaSandbox('auth_iv');

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

            SabpaisaSandboxOrder::where('order_id', $clientTxnId)->update([
                'status' => in_array(strtolower($status), ['success', 'paid']) ? 'completed' : 'failed',
                'request_response' => $decText,
            ]);

            $order = SabpaisaSandboxOrder::with('user')->where('order_id', $clientTxnId)->first();

            $sendData = json_encode([
                'order_id' => $order->order_id,
                'tnx_id' => $order->txn_id,
                'amount' => $order->amount,
                'status' => $order->status
            ]);

            $backUrl = "{$order->user->callback_url}?response={$sendData}";

            return redirect()->to($backUrl);
        }

        return response()->json([
            'error' => 'Payment failed or cancelled',
            'message' => 'Unable to process payment',
        ], 402);
    }

    public function status(Request $request)
    {
        $userId = config('services.sabpaisa.user.id');

        $validator = Validator::make($request->all(), [
            'tnx_id' => ['required', 'string', Rule::exists('sabpaisa_sandbox_orders', 'tnx_id')->where('user_id', $userId)],
        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $input = $validator->validated();

        $order = SabpaisaSandboxOrder::where('user_id', $userId)
            ->where('tnx_id', $input['tnx_id'])
            ->first();

        return response()->json([
            'order_id' => $order->order_id,
            'tnx_id' => $order->txn_id,
            'amount' => $order->amount,
            'status' => $order->status,
            'payer_name' => $order->payer_name,
            'payer_email' => $order->payer_email,
            'payer_mobile' => $order->payer_mobile,
        ]);
    }

    public function webhook(Request $request, SabpaisaAuthSandbox $sabpaisaAuth)
    {
        $data = $request->input('encData');

        $authKey = sabpaisaSandbox('auth_key');
        $authIV = sabpaisaSandbox('auth_iv');

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

        SabpaisaSandboxOrder::where('order_id', $clientTxnId)->update([
            'status' => in_array(strtolower($status), ['success', 'paid']) ? 'completed' : 'failed',
            'request_response' => $decText,
        ]);

        $order = SabpaisaSandboxOrder::with('user')->where('order_id', $clientTxnId)->first();

        if ($order->user->notify_url != null) {

            Http::post($order->user->notify_url, [
                'order_id' => $order->order_id,
                'amount' => $order->amount,
                'status' => in_array(strtolower($status), ['success', 'paid']) ? 'completed' : 'failed'
            ]);
        }

        return response('OK', 200);
    }
}

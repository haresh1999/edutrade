<?php

namespace App\Http\Controllers;

use App\Classes\PaytmChecksum;
use App\Models\PaytmSandboxOrder;
use App\Models\PaytmUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PaytmSandboxController extends Controller
{
    public function token()
    {
        $userId = config('services.paytm.user.id');

        $token = str()->random(100);

        PaytmUser::where('id', $userId)->update(['refresh_token' => $token]);

        return response()->json([
            'refresh_token' => $token
        ]);
    }

    public function create(Request $request)
    {
        $userId = config('services.paytm.user.id');

        $input = $request->validate([
            'order_id' => ['required', 'string', Rule::unique('paytm_sandbox_orders', 'order_id')->where('user_id', $userId)],
            'amount' => ['required', 'numeric', 'min:1'],
            'payer_name' => ['required', 'string', 'max:255'],
            'payer_email' => ['required', 'email', 'max:255'],
            'payer_mobile' => ['required', 'digits_between:9,11'],
        ]);

        $actionUrl = env('APP_URL') . '/paytm/sandbox/request';

        $token = str()->random(100);

        PaytmUser::where('id', $userId)->update(['refresh_token' => $token]);

        return view('paytm.request', compact('actionUrl', 'input', 'userId'));
    }

    public function request(Request $request)
    {
        $userId = $request->user_id;

        $input = $request->validate([
            'order_id' => ['required', 'string', Rule::unique('paytm_sandbox_orders', 'order_id')->where('user_id', $userId)],
            'amount' => ['required', 'numeric', 'min:1'],
            'payer_name' => ['required', 'string', 'max:255'],
            'payer_email' => ['required', 'email', 'max:255'],
            'payer_mobile' => ['required', 'digits_between:9,11'],
            'user_id' => ['required', 'integer', 'exists:paytm_users,id'],
        ]);

        $linkName = preg_replace('/[^A-Za-z0-9 _-]/', '', $input['payer_name']);

        $linkName = trim($linkName);

        $body = [
            'mid' => setting('mid'),
            'linkType' => 'FIXED',
            'linkDescription' => 'Test Payment',
            'linkName' => $linkName,
            'amount' => (string)$input['amount'],
            'customerContact' => [
                'customerName' => $input['payer_name'],
                'customerEmail' => $input['payer_email'],
                'customerMobile' => $input['payer_mobile']
            ],
            'linkOrderId' => $input['order_id'],
            'singleTransactionOnly' => true,
            'redirectionUrlSuccess' => env('PAYTM_SANDBOX_REDIRECT_URL') . "?order_id={$input['order_id']}",
            'redirectionUrlFailure' => env('PAYTM_SANDBOX_REDIRECT_URL') . "?order_id={$input['order_id']}"
        ];

        $signature = PaytmChecksum::generateSignature(
            json_encode($body, JSON_UNESCAPED_SLASHES),
            setting('mkey')
        );

        $payload = [
            "head" => [
                "version" => "v2",
                "timestamp" => round(microtime(true) * 1000),
                "channelId" => "WEB",
                "tokenType" => "AES",
                "clientId" => setting('cid'),
                "signature" => $signature
            ],
            "body" => $body
        ];

        $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://securestage.paytmpayments.com/link/create",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payloadJson,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ]
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $res = json_decode($response, true);

        $create['user_id'] = $userId;
        $create['order_id'] = $input['order_id'];
        $create['amount'] = $input['amount'];
        $create['payer_name'] = $input['payer_name'];
        $create['payer_email'] = $input['payer_email'];
        $create['payer_mobile'] = $input['payer_mobile'];
        $create['request_response'] = $response;

        if (isset($res['body']['shortUrl'])) {

            $redirectTo = $res['body']['shortUrl'];

            $create['status'] = 'pending';

            PaytmSandboxOrder::create($create);

            return redirect()->to($redirectTo);
        }

        $create['status'] = 'failed';

        PaytmSandboxOrder::create($create);

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create payment link',
            'details' => $response
        ], 401);
    }

    public function status(Request $request)
    {
        $userId = config('services.paytm.user.id');

        $validator = Validator::make($request->all(), [
            'tnx_id' => ['required', 'string', Rule::exists('paytm_sandbox_orders', 'tnx_id')->where('user_id', $userId)],
        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $input = $validator->validated();

        $order = PaytmSandboxOrder::where('user_id', $userId)
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

    private function clientCallback($url, $order)
    {
        $data = [
            'order_id' => $order->order_id,
            'tnx_id' => $order->tnx_id,
            'amount' => $order->amount,
            'status' => $order->status,
            'payer_name' => $order->payer_name,
            'payer_email' => $order->payer_email,
            'payer_mobile' => $order->payer_mobile,
        ];

        return Http::post($url, $data);
    }

    public function callback(Request $request)
    {
        $order = PaytmSandboxOrder::with('user')
            ->where('order_id', $request->order_id)
            ->first();

        abort_if(is_null($order), 404);

        if ($request->has('order_id')) {

            $body = [
                "mid" => setting('mid'),
                "orderId" => $order->order_id
            ];

            $signature = PaytmChecksum::generateSignature(
                json_encode($body, JSON_UNESCAPED_SLASHES),
                setting('mkey')
            );

            $payload = [
                "body" => $body,
                "head" => [
                    "tokenType" => "AES",
                    "signature" => $signature
                ],
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://securestage.paytmpayments.com/v3/order/status",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
                CURLOPT_POSTFIELDS => json_encode($payload)
            ]);

            $response = curl_exec($curl);

            $res = json_decode($response);

            if (curl_errno($curl)) {

                curl_close($curl);

                $redirectUrl = $order->user->redirect_url;

                $callbackUrl = $order->user->callback_url;

                $order->update([
                    'status' => 'failed',
                    'request_response' => $response,
                ]);

                $this->clientCallback($callbackUrl, $order);

                return redirect()->to($redirectUrl);
            } else {

                curl_close($curl);

                $status = $res->body->resultInfo->resultStatus;

                if (strtolower($status) == 'txn_success') {

                    $redirectUrl = $order->user->redirect_url;

                    $callbackUrl = $order->user->callback_url;

                    $order->update([
                        'status' => 'completed',
                        'request_response' => $response,
                    ]);

                    $this->clientCallback($callbackUrl, $order);

                    return redirect()->to($redirectUrl);
                }

                if (strtolower($status) == 'pending') {

                    $redirectUrl = $order->user->redirect_url;

                    $callbackUrl = $order->user->callback_url;

                    $order->update([
                        'status' => 'pending',
                        'request_response' => $response,
                    ]);

                    $this->clientCallback($callbackUrl, $order);

                    return redirect()->to($redirectUrl);
                }

                $redirectUrl = $order->user->redirect_url;

                $callbackUrl = $order->user->callback_url;

                $order->update([
                    'status' => 'failed',
                    'request_response' => $response,
                ]);

                $this->clientCallback($callbackUrl, $order);

                return redirect()->to($redirectUrl);
            }
        }
    }

    public function webhook()
    {
        //
    }
}

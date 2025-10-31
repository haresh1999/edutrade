<?php

namespace App\Http\Controllers;

use App\Models\PhonepeOrder;
use App\Models\PhonepeUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PhonepeController extends Controller
{
    public function token()
    {
        $userId = config('services.phonepe.user.id');

        $token = str()->random(100);

        PhonepeUser::where('id', $userId)->update(['refresh_token' => $token]);

        return response()->json([
            'refresh_token' => $token
        ]);
    }

    public function create(Request $request)
    {
        $userId = config('services.phonepe.user.id');

        $input = $request->validate([
            'order_id' => ['required', 'string', Rule::unique('phonepe_orders', 'order_id')->where('user_id', $userId)],
            'amount' => ['required', 'numeric', 'min:1'],
            'payer_name' => ['required', 'string', 'max:255'],
            'payer_email' => ['required', 'email', 'max:255'],
            'payer_mobile' => ['required', 'digits_between:9,11'],
        ]);

        $actionUrl = env('APP_URL') . '/phonepe/request';

        return view('phonepe.request', compact('actionUrl', 'input', 'userId'));
    }

    private function getAccessToken()
    {
        $url = 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';

        $fields = [
            'client_id' => setting('client_id'),
            'client_version' => setting('client_version'),
            'client_secret' => setting('client_secret'),
            'grant_type' => setting('grant_type')
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

        $response = json_decode($response);

        return $response->access_token;
    }

    public function request(Request $request)
    {
        $userId = $request->user_id;

        $input = $request->validate([
            'order_id' => ['required', 'string', Rule::unique('phonepe_orders', 'order_id')->where('user_id', $userId)],
            'amount' => ['required', 'numeric', 'min:1'],
            'payer_name' => ['required', 'string', 'max:255'],
            'payer_email' => ['required', 'email', 'max:255'],
            'payer_mobile' => ['required', 'digits_between:9,11'],
            'user_id' => ['required', 'integer', 'exists:phonepe_users,id'],
        ]);

        $payload = [
            'merchantOrderId' => $input['order_id'],
            'amount' => ($input['amount'] * 100),
            'paymentFlow' => [
                'type' => 'PG_CHECKOUT',
                'message' => 'Proceed to complete the payment',
                'merchantUrls' => [
                    'redirectUrl' => env('PHONEPE_REDIRECT_URL') . "?order_id={$input['order_id']}",
                ],
            ],
        ];

        $accessToken = $this->getAccessToken();

        $url = 'https://api.phonepe.com/apis/pg/checkout/v2/pay';

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: O-Bearer ' . $accessToken,
            ],
        ]);

        $response = curl_exec($ch);

        curl_close($ch);

        $redirectUrl = json_decode($response, true);

        $create['user_id'] = $userId;
        $create['order_id'] = $input['order_id'];
        $create['amount'] = $input['amount'];
        $create['payer_name'] = $input['payer_name'];
        $create['payer_email'] = $input['payer_email'];
        $create['payer_mobile'] = $input['payer_mobile'];
        $create['request_response'] = $response;

        if (isset($redirectUrl['redirectUrl'])) {

            $redirectTo = $redirectUrl['redirectUrl'];

            $create['status'] = 'pending';

            PhonepeOrder::create($create);

            return redirect()->to($redirectTo);
        }

        $create['status'] = 'failed';

        PhonepeOrder::create($create);

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create payment link',
            'details' => $response
        ], 401);
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

        $order = PhonepeOrder::where('user_id', $userId)
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
        $order = PhonepeOrder::with('user')
            ->where('order_id', $request->order_id)
            ->first();

        abort_if(is_null($order), 404);

        if ($request->has('order_id')) {

            $orderId = $request->order_id;

            $url = "https://api.phonepe.com/apis/pg/checkout/v2/order/{$orderId}/status";

            $accessToken = $this->getAccessToken();

            $headers = [
                'Content-Type: application/json',
                'Authorization: O-Bearer ' . $accessToken,
            ];

            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPGET => true,
                CURLOPT_HTTPHEADER => $headers,
            ]);

            $response = curl_exec($ch);

            $res = json_decode($response);

            if (curl_errno($ch)) {

                curl_close($ch);

                $redirectUrl = $order->user->redirect_url;

                $callbackUrl = $order->user->callback_url;

                $order->update([
                    'status' => 'failed',
                    'request_response' => $response,
                ]);

                $this->clientCallback($callbackUrl, $order);

                return redirect()->to($redirectUrl);
            } else {

                curl_close($ch);

                if (strtolower($res->state) == 'completed') {

                    $redirectUrl = $order->user->redirect_url;

                    $callbackUrl = $order->user->callback_url;

                    $order->update([
                        'status' => 'completed',
                        'request_response' => $response,
                    ]);

                    $this->clientCallback($callbackUrl, $order);

                    return redirect()->to($redirectUrl);
                }

                if (strtolower($res->state) == 'pending') {

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

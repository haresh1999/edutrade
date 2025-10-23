<?php

namespace App\Http\Controllers;

use App\Classes\SabpaisaAuth;
use App\Http\Requests\SabpaisaRequest;
use App\Models\SabpaisaOrder;
use Illuminate\Http\Request;

class SabpaisaController extends Controller
{
    public function request(SabpaisaRequest $request)
    {
        $input = $request->validated();

        $input['currency'] = 'INR';
        $input['mcc'] = 5137;
        $input['channel_id'] = 'W';
        $input['callback_url'] = 'https://edutrade.in/payment/sabpaisa/callback';
        $input['class'] = 'VIII';
        $input['roll'] = '1008';
        $input['url'] = sabpaisa('url');
        $input['user_id'] = config('services.sabpaisa.user.id');

        $clientCode = sabpaisa('client_code');
        $username = sabpaisa('username');
        $password = sabpaisa('password');
        $authKey = sabpaisa('auth_key');
        $authIV = sabpaisa('auth_iv');

        $input['enc_data'] = "?clientCode=" . $clientCode . "&transUserName=" . $username . "&transUserPassword=" . $password .
            "&payerName=" . $input['payer_name'] . "&payerMobile=" . $input['payer_mobile'] . "&payerEmail=" . $input['payer_email'] . "&clientTxnId=" . $input['order_id'] . "&amount=" . $input['amount'] . "&amountType=" . $input['currency'] . "&mcc=" . $input['mcc'] . "&channelId=" . $input['channel_id'] .
            "&callbackUrl=" . $input['callback_url'] . "&udf1=" . $input['class'] . "&udf2=" . $input['roll'];

        $sabpaisaAuth = new SabpaisaAuth();

        $data = $sabpaisaAuth->encrypt($authKey, $authIV, $input['enc_data']);

        SabpaisaOrder::create($input);

        return view('sabpaisa.request', compact('input', 'clientCode'));
    }

    public function status()
    {
        
    }

    public function callback()
    {
        
    }
}

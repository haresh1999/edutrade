<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\SabpaisaAuthSandbox;
use App\Http\Requests\SabpaisaSandboxRequest;
use App\Models\SabpaisaSandboxOrder;

class SabpaisaSandboxController extends Controller
{
    public function request(SabpaisaSandboxRequest $request, SabpaisaAuthSandbox $sabpaisaAuth)
    {
        $input = $request->validated();

        $input['currency'] = 'INR';
        $input['mcc'] = 5137;
        $input['channel_id'] = 'W';
        $input['callback_url'] = 'https://edutrade.in/payment/sabpaisa/response.php';
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

    public function status()
    {
        
    }

    public function callback()
    {
        
    }
}

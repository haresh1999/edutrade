<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayoutController extends Controller
{
    public function request()
    {
        $url = "https://softtechapi.in/api/Payout/v3/Payout";

        $payload = [
            'mode' => 'IMPS',
            'remarks' => 'rtesgt',
            'amount' => '1',
            'type' => '',
            'bene_name' => 'CHAUHAN HARESHBHAI SURESHBHAI',
            'bene_mobile' => '9737314639',
            'bene_email' => 'hareshchauhan566@gmail.com',
            'bene_acc' => '6550030962',
            'bene_ifsc' => 'KKBK00003018',
            'bene_acc_type' => 'saving',
            'refid' => uniqid('TNX'),
            'bene_bank_name' => 'Kotak Mahindra Bank',
            'otp' => rand(111111, 999999)
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "x-qro-userid: 9656132740",
            "x-qro-apikey: 1CBAE4927FC2438C80B689170266442A"
        ]);

        $response = curl_exec($ch);
        $err = curl_error($ch);

        curl_close($ch);

        if ($err) {
            echo "cURL Error #: " . $err;
        } else {
            echo $response;
        }

        return response()->json(['message' => 'Payout request received']);
    }
}

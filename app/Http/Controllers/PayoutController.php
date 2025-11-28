<?php

namespace App\Http\Controllers;

class PayoutController extends Controller
{
    public function request()
    {
        $curl = curl_init();

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

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://softtechapi.in/api/Payout/v3/Payout',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-qro-userid: 9656132740',
                'x-qro-apikey: 1CBAE4927FC2438C80B689170266442A'
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return response()->json(json_decode($response));
    }
}

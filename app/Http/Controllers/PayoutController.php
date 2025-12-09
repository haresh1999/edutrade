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
            'amount' => '100',
            'type' => '',
            'bene_name' => 'CHAUHAN HARESHBHAI SURESHBHAI',
            'bene_mobile' => '9737314639',
            'bene_email' => 'hareshchauhan566@gmail.com',
            'bene_acc' => '6550030962',
            'bene_ifsc' => 'KKBK0003018',
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
                'x-qro-userid: SBS537526',
                'x-qro-apikey: 1CBAE4927FC2438C80B689170266442A'
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return response()->json(json_decode($response));
    }

    public function status($refId)
    {
        $payload = json_encode([
            'refid' => $refId
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://softtechapi.in/api/payout/v3/StatusEnquiry',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-qro-userid: SBS537526',
                'x-qro-apikey: 1CBAE4927FC2438C80B689170266442A'
            ],
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return response()->json(json_decode($response));
    }
}

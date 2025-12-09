<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PayoutController extends Controller
{
    public function request(Request $request)
    {
        $rules = Validator::make($request->all(), [
            'mode' => ['required', 'in:imps'],
            'amount' => ['required', 'numeric', 'min:100'],
            'bene_name' => ['required', 'max:100'],
            'bene_mobile' => ['required', 'digits_between:9,11'],
            'bene_email' => ['required', 'email'],
            'bene_acc' => ['required', 'numeric'],
            'bene_ifsc' => ['required', 'max:50'],
            'bene_acc_type' => ['required', 'in:saving,current'],
            'bene_bank_name' => ['required', 'max:100'],
        ]);

        if ($rules->fails()) {

            return response()->json($rules->errors()->toArray());
        }

        $input = $rules->validated();

        $curl = curl_init();

        $payload = [
            'mode' => strtoupper($input['mode']),
            'remarks' => 'rtesgt',
            'amount' => $input['amount'],
            'type' => '',
            'bene_name' => $input['bene_name'],
            'bene_mobile' => $input['bene_mobile'],
            'bene_email' => $input['bene_email'],
            'bene_acc' => $input['bene_acc'],
            'bene_ifsc' => $input['bene_ifsc'],
            'bene_acc_type' => $input['bene_acc_type'],
            'refid' => uniqid('TNX'),
            'bene_bank_name' => $input['bene_bank_name'],
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

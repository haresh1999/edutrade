<?php

if (isset($_GET['token'])) {

    function base64url_decode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    function decryptData(string $encryptedData, string $secretKey): ?array
    {
        
        $data = base64url_decode($encryptedData);
    
        $iv = substr($data, 0, 16);
        
        $ciphertext = substr($data, 16);
    
        $plaintext = openssl_decrypt($ciphertext, 'AES-256-CBC', $secretKey, OPENSSL_RAW_DATA, $iv);
    
        return json_decode($plaintext, true);
    }

    function getAccessToken(){
    
        $url = 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';
        
        $fields = [
            'client_id' => 'SU2509231940115728161187',
            'client_version' => '1.0',
            'client_secret' => 'ef6c3f40-db7b-4839-b97d-cc114df6d895',
            'grant_type' => 'client_credentials'
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
        
        return json_decode($response)->access_token;
    }
    
    $data = decryptData($_GET['token'],'EXAMPLE@123');
    
    $access_token = getAccessToken();
    
    $url = "https://api.phonepe.com/apis/pg/checkout/v2/order/{$data['order_id']}/status";
    
    $headers = [
        'Content-Type: application/json',
        'Authorization: O-Bearer ' . $access_token,
    ];
    
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPGET => true,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        
        echo 'Error: ' . curl_error($ch);
        
        curl_close($ch);
        
        header('Location: '.$data['failed_url']);
        exit;
        
    } else {
        
        $res = json_decode($response);
        
        curl_close($ch);
        
        if(strtolower($res->state) == 'completed'){
            
            $sendData = json_encode([
                'order_id' => $data['order_id'],
                'amount' => $data['amount'],
                'status' => 'completed'
            ]);
            
            $backUrl = "{$data['success_url']}?response={$sendData}";
            
            header('Location: '.$backUrl);
            exit;
            
        }elseif(strtolower($res->state) == 'pending'){
            
            $sendData = json_encode([
                'order_id' => $data['order_id'],
                'amount' => $data['amount'],
                'status' => 'pending'
            ]);
            
            $backUrl = "{$data['success_url']}?response={$sendData}";
            
            header('Location: '.$backUrl);
            exit;
            
        }else{
            
            $sendData = json_encode([
                'order_id' => $data['order_id'],
                'amount' => $data['amount'],
                'status' => 'failed'
            ]);
            
            $backUrl = "{$data['failed_url']}?response={$sendData}";
            
            header('Location: '.$backUrl);
            exit;
        }
    }
}else{
    
     http_response_code(401);

    die(json_encode([
        'status' => 'error',
        'message' => 'Something went wrong!, Unable to handle response '
    ]));  
}
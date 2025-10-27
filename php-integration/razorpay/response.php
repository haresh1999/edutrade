<?php

if (isset($_GET['token']) && isset($_GET['razorpay_payment_link_id'])) {

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
    
  
    $data = decryptData($_GET['token'],'EXAMPLE@123');
    
    $payment_link_id = $_GET['razorpay_payment_link_id'] ?? '';
    $payment_link_ref = $_GET['razorpay_payment_link_reference_id'] ?? '';
    $payment_id = $_GET['razorpay_payment_id'] ?? '';
    $payment_status = $_GET['razorpay_payment_link_status'] ?? '';
    $signature = $_GET['razorpay_signature'] ?? ''; // sometimes not sent for payment links

     if($payment_status === 'paid'){
        
        $sendData = json_encode([
            'order_id' => $data['order_id'],
            'payment_id' => $payment_id,
            'amount' => $data['amount'],
            'status' => 'paid'
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

}else{
    
    http_response_code(401);

    die(json_encode([
        'status' => 'error',
        'message' => 'Something went wrong!, Unable to handle response '
    ])); 
}
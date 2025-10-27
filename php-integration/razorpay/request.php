<?php

$clients = [
    'EDUTRADE' => 'b88ffa4e-9a77-4c04-b545-c6aac9ecb7c9',
];

if (
    isset($_GET['amount'], $_GET['success_url'], $_GET['failed_url'], $_GET['order_id'], $_GET['client_id'], $_GET['client_secret']) &&
    !empty($_GET['amount']) && !empty($_GET['success_url']) && !empty($_GET['failed_url']) && !empty($_GET['order_id']) &&
    is_numeric($_GET['amount']) && floatval($_GET['amount']) > 0 &&
    filter_var($_GET['success_url'], FILTER_VALIDATE_URL) &&
    filter_var($_GET['failed_url'], FILTER_VALIDATE_URL)
) {

    if (!array_key_exists($_GET['client_id'], $clients)) {

        http_response_code(401);

        die(json_encode([
            'status' => 'error',
            'message' => 'Invalid client name'
        ]));
    }

    if ($clients[$_GET['client_id']] !== $_GET['client_secret']) {

        http_response_code(403);

        die(json_encode([
            'status' => 'error',
            'message' => 'Invalid API key'
        ]));
    }

    function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    function encryptData(array $data, string $secretKey): string
    {
        $plaintext = json_encode($data);

        $iv = openssl_random_pseudo_bytes(16);

        $ciphertext = openssl_encrypt($plaintext, 'AES-256-CBC', $secretKey, OPENSSL_RAW_DATA, $iv);

        return base64url_encode($iv . $ciphertext);
    }

    $data['order_id'] = $_GET['order_id'];
    $data['amount'] = ($_GET['amount'] * 100);
    $data['success_url'] = $_GET['success_url'];
    $data['failed_url'] = $_GET['failed_url'];

    $enc = encryptData($data, 'EXAMPLE@123');

    $key_id = "rzp_live_RTIzeZS7Q22shx";
    $key_secret = "nca3Agw8XlpW95Hfmp4et4al";

    $url = "https://api.razorpay.com/v1/payment_links";

    $amount = (10 * 100);

    $data = [
        "amount" => $data['amount'],
        "currency" => "INR",
        "accept_partial" => false,
        "description" => "Test payment link via cURL",
        "customer" => [
            "name" => "John Doe",
            "email" => "john@example.com",
            "contact" => "9876543210"
        ],
        "notify" => [
            "sms" => true,
            "email" => true
        ],
        "reminder_enable" => true,
        "callback_url" => "https://edutrade.in/payment/razorpay/response.php?token={$enc}",
        "callback_method" => "get"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $key_id . ":" . $key_secret);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {

        http_response_code(401);

        die(json_encode([
            'status' => 'error',
            'message' => curl_error($ch)
        ]));
    }

    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['short_url'])) {

        header('Location: ' . $result['short_url']);
        exit;
        
    } else {

        http_response_code(401);

        die(json_encode([
            'status' => 'error: ❌ Failed to generate link',
            'message' => print_r($result, true),
        ]));
    }
} else {

    http_response_code(401);

    die(json_encode([
        'status' => 'error',
        'message' => 'Invalid payload given'
    ]));
}

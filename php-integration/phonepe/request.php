<?php

$clients = [
    'EDUTRADE' => 'b88ffa4e-9a77-4c04-b545-c6aac9ecb7c9',
];

if (
    isset($_GET['amount'], $_GET['success_url'], $_GET['failed_url'], $_GET['order_id'], $_GET['client_id'], $_GET['client_secret'], $_GET['phonepe_accept_request']) &&
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

    function getAccessToken()
    {
        $url = 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';

        $fields = [
            'client_id' => 'SU2509231940115728161187',
            'client_version' => 1,
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

    $data['order_id'] = $_GET['order_id'];
    $data['amount'] = $_GET['amount'];
    $data['success_url'] = $_GET['success_url'];
    $data['failed_url'] = $_GET['failed_url'];

    $enc = encryptData($data, 'EXAMPLE@123');

    $payload = [
        'merchantOrderId' => uniqid('ORDER_'),
        'amount' => $data['amount'],
        'paymentFlow' => [
            'type' => 'PG_CHECKOUT',
            'message' => 'Proceed to complete the payment',
            'merchantUrls' => [
                'redirectUrl' => "https://edutrade.in/payment/phonepe/response.php?token={$enc}",
            ],
        ],
    ];

    $url = 'https://api.phonepe.com/apis/pg/checkout/v2/pay';

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: O-Bearer ' . getAccessToken(),
        ],
    ]);

    $response = curl_exec($ch);

    curl_close($ch);

    $redirectUrl = json_decode($response)->redirectUrl;

    header('Location: ' . $redirectUrl);
    exit;
}

if (
    isset($_GET['amount'], $_GET['success_url'], $_GET['failed_url'], $_GET['order_id'], $_GET['client_id'], $_GET['client_secret']) &&
    !empty($_GET['amount']) && !empty($_GET['success_url']) && !empty($_GET['failed_url']) && !empty($_GET['order_id']) &&
    is_numeric($_GET['amount']) && floatval($_GET['amount']) > 0 &&
    filter_var($_GET['success_url'], FILTER_VALIDATE_URL) &&
    filter_var($_GET['failed_url'], FILTER_VALIDATE_URL)
) {
?>
    <form id="payment-form" action="https://edutrade.in/payment/phonepe/request.php?phonepe_accept_request=1" method="get">
        <input type="hidden" name="phonepe_accept_request" value="1">
        <input type="hidden" name="order_id" value="<?php echo $_GET['order_id'] ?>">
        <input type="hidden" name="amount" value="<?php echo ($_GET['amount'] * 100) ?>">
        <input type="hidden" name="success_url" value="<?php echo $_GET['success_url'] ?>">
        <input type="hidden" name="failed_url" value="<?php echo $_GET['failed_url'] ?>">
        <input type="hidden" name="client_id" value="<?php echo $_GET['client_id'] ?>">
        <input type="hidden" name="client_secret" value="<?php echo $_GET['client_secret'] ?>">
        <p>Please wait while we redirecting you...</p>
    </form>
    <script>
        document.getElementById('payment-form').submit();
    </script>
<?php } else {
    http_response_code(401);

    die(json_encode([
        'status' => 'error',
        'message' => 'Invalid payload given'
    ]));
} ?>
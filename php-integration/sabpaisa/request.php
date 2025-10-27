<?php

// include 'authentication.php'; // for sandbox
include 'auth.php';  //for production

$clients = [
    'EDUTRADE' => 'b88ffa4e-9a77-4c04-b545-c6aac9ecb7c9',
];

if (
    isset($_GET['amount'], $_GET['success_url'], $_GET['failed_url'], $_GET['order_id'], $_GET['client_id'], $_GET['client_secret'], $_GET['payer_name'], $_GET['payer_email'], $_GET['payer_mobile']) &&
    !empty($_GET['amount']) && !empty($_GET['success_url']) && !empty($_GET['failed_url']) && !empty($_GET['order_id']) && !empty($_GET['client_id']) && !empty($_GET['client_secret']) && !empty($_GET['payer_name']) && !empty($_GET['payer_email'])
    && !empty($_GET['payer_mobile']) && is_numeric($_GET['amount']) && floatval($_GET['amount']) > 0 &&
    filter_var($_GET['payer_email'], FILTER_VALIDATE_EMAIL) &&
    preg_match('/^[0-9]{10,15}$/', $_GET['payer_mobile']) &&
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
    $data['amount'] = $_GET['amount'];
    $data['success_url'] = $_GET['success_url'];
    $data['failed_url'] = $_GET['failed_url'];
    $data['payer_name'] = $_GET['payer_name'];
    $data['payer_email'] = $_GET['payer_email'];
    $data['payer_mobile'] = $_GET['payer_mobile'];

    $enc = encryptData($data, 'EXAMPLE@123');

    $encData = null;

    $clientCode = 'SHA9I6'; //for production
    $username = 'ikondubai@gmail.com'; //for production
    $password = 'SHA9I6_SP23560'; //for production
    $authKey = 'HT3sbrw8jrzKBmZqt0Wr7iFWZaq9mWy5j8d/Yu7WQxE='; //for production
    $authIV = '0Op4vCNftDe4i0OwdOfP99BW2qz8KCfOzyXz66VWL4L2q50/uzB2ygpF6Xq+2Vea'; //for production
    $url = 'https://securepay.sabpaisa.in/SabPaisa/sabPaisaInit?v=1'; //for production

    // $clientCode='DCRBP'; // for sandbox
    // $username='userph.jha_3036'; // for sandbox
    // $password='DBOI1_SP3036'; // for sandbox
    // $authKey='0jeOYcu3UnfmWyLC'; // for sandbox
    // $authIV='C28LAmGxXTqmK0QJ'; // for sandbox
    // $url = 'https://stage-securepay.sabpaisa.in/SabPaisa/sabPaisaInit?v=1'; // for sandbox

    $payerName = $data['payer_name'];
    $payerEmail = $data['payer_email'];
    $payerMobile = $data['payer_mobile'];
    $clientTxnId = rand(111111111, 999999999);
    $amount = $data['amount'];
    $amountType = 'INR';
    $mcc = 5137;
    $channelId = 'W';
    $callbackUrl = "https://edutrade.in/payment/sabpaisa/response.php";
    $Class = 'VIII';
    $Roll = '1008';
    $order_id = $clientTxnId;

    $conn = new mysqli("localhost", "u680239250_payment", "Sm2^fe#hgPq", "u680239250_payment");
    $sql = "INSERT INTO `order` (`order_id`, `token`) VALUES ('$order_id', '$enc')";
    $conn->query($sql);
    $conn->close();

    $encData = "?clientCode=" . $clientCode . "&transUserName=" . $username . "&transUserPassword=" . $password .
        "&payerName=" . $payerName . "&payerMobile=" . $payerMobile . "&payerEmail=" . $payerEmail . "&clientTxnId=" . $clientTxnId . "&amount=" . $amount . "&amountType=" . $amountType . "&mcc=" . $mcc . "&channelId=" . $channelId .
        "&callbackUrl=" . $callbackUrl . "&udf1=" . $Class . "&udf2=" . $Roll;

    // $AesCipher = new AesCipher(); // for sandbox
    // $data = $AesCipher->encrypt($authKey, $authIV, $encData); // for sandbox

    $AES256HMACSHA384HEX = new AES256HMACSHA384HEX(); //for production
    $data = $AES256HMACSHA384HEX->encrypt($authKey, $authIV, $encData); //for production

} else {

    http_response_code(401);

    die(json_encode([
        'status' => 'error',
        'message' => 'Invalid payload given'
    ]));
}

?>

<form id="payment-form" action="<?php echo $url; ?>" method="post">
    <input type="hidden" name="encData" value="<?php echo $data ?>" id="frm1">
    <input type="hidden" name="clientCode" value="<?php echo $clientCode ?>" id="frm2">
    <p>Please wait while we redirecting you...</p>
</form>

<script>
    document.getElementById('payment-form').submit();
</script>
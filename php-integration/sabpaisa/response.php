<?php

// include 'authentication.php';
include 'auth.php';

if (isset($_REQUEST['encResponse'])) {

    $query = $_REQUEST['encResponse'];

    $authKey = 'HT3sbrw8jrzKBmZqt0Wr7iFWZaq9mWy5j8d/Yu7WQxE=';
    $authIV = '0Op4vCNftDe4i0OwdOfP99BW2qz8KCfOzyXz66VWL4L2q50/uzB2ygpF6Xq+2Vea';

    $decText = null;
    $AES256HMACSHA384HEX = new AES256HMACSHA384HEX();
    $decText = $AES256HMACSHA384HEX->decrypt($authKey, $authIV, $query);
    $token = strtok($decText, "&");
    $i = 0;

    while ($token !== false) {
        $i = $i + 1;
        $token1 = strchr($token, "=");
        $token = strtok("&");
        $fstr = ltrim($token1, "=");

        if ($i == 1)
            $payerName = $fstr;
        if ($i == 2)
            $payerEmail = $fstr;
        if ($i == 3)
            $payerMobile = $fstr;
        if ($i == 4)
            $clientTxnId = $fstr;
        if ($i == 5)
            $payerAddress = $fstr;
        if ($i == 6)
            $amount = $fstr;
        if ($i == 7)
            $clientCode = $fstr;
        if ($i == 8)
            $paidAmount = $fstr;
        if ($i == 9)
            $paymentMode = $fstr;
        if ($i == 10)
            $bankName = $fstr;
        if ($i == 11)
            $amountType = $fstr;
        if ($i == 12)
            $status = $fstr;
        if ($i == 13)
            $statusCode = $fstr;
        if ($i == 14)
            $challanNumber = $fstr;
        if ($i == 15)
            $sabpaisaTxnId = $fstr;
        if ($i == 16)
            $sabpaisaMessage = $fstr;
        if ($i == 17)
            $bankMessage = $fstr;
        if ($i == 18)
            $bankErrorCode = $fstr;
        if ($i == 19)
            $sabpaisaErrorCode = $fstr;
        if ($i == 20)
            $bankTxnId = $fstr;
        if ($i == 21)
            $transDate = $fstr;
    }

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

    $conn = new mysqli("localhost", "u680239250_payment", "Sm2^fe#hgPq", "u680239250_payment");

    $query = "SELECT * FROM `order` WHERE order_id='$clientTxnId'";

    $result = $conn->query($query);

    $row = $result->fetch_assoc();

    $data = decryptData($row['token'], 'EXAMPLE@123');

    $conn->close();

    if (in_array(strtolower($status), ['success', 'paid'])) {

        $sendData = json_encode([
            'order_id' => $data['order_id'],
            'payment_id' => $sabpaisaTxnId,
            'amount' => $amount,
            'status' => 'paid'
        ]);

        $backUrl = "{$data['success_url']}?response={$sendData}";
        header('Location: ' . $backUrl);
        exit;
    } else {

        $sendData = json_encode([
            'order_id' => $data['order_id'],
            'amount' => $amount,
            'status' => 'failed'
        ]);

        $backUrl = "{$data['failed_url']}?response={$sendData}";
        header('Location: ' . $backUrl);
        exit;
    }
} else {

    http_response_code(401);

    die(json_encode([
        'status' => 'error',
        'message' => 'Something went wrong!, Unable to handle response '
    ]));
}

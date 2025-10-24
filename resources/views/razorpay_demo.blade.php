<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Razorpay Payment Request</title>
</head>
<body>

    <h1>Sandbox Payment</h1>

    <form action="{{ url('/razorpay/sandbox/request') }}" method="post">
        <input type="text" name="client_id" value="edutrade">
        <input type="text" name="client_secret" value="e6395a8c-1566-496a-9623-d8ee4529d1a9">
        <input type="text" name="payer_name" value="Haresh">
        <input type="text" name="payer_email" value="hareshc1999@gmail.com">
        <input type="text" name="payer_mobile" value="9106029220">
        <input type="text" name="amount" value="1">
        <input type="text" name="order_id" value="{{ Str::random(10) }}">
        <input type="submit" value="Paynow">
    </form>

    {{-- Live --}}

    <h1>Live Payment</h1>

    <form action="{{ url('/razorpay/request') }}" method="post">
        <input type="text" name="client_id" value="edutrade">
        <input type="text" name="client_secret" value="3cf6119c-18c9-411e-94b8-aa521588ec9d">
        <input type="text" name="payer_name" value="Haresh">
        <input type="text" name="payer_email" value="hareshc1999@gmail.com">
        <input type="text" name="payer_mobile" value="9106029220">
        <input type="text" name="amount" value="1">
        <input type="text" name="order_id" value="{{ Str::random(10) }}">
        <input type="submit" value="Paynow">
    </form>
</body>
</html>
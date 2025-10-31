<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Phonepe Payment Request</title>
</head>
<body>

    <h1>Sandbox Payment</h1>

    <form action="{{ url('/phonepe/sandbox/create') }}" method="post">
        <input type="text" name="refresh_token" value="">
        <input type="text" name="payer_name" value="Haresh">
        <input type="text" name="payer_email" value="hareshc1999@gmail.com">
        <input type="text" name="payer_mobile" value="9106029220">
        <input type="text" name="amount" value="1">
        <input type="text" name="order_id" value="{{ Str::random(10) }}">
        <input type="submit" value="Paynow">
    </form>

    {{-- Live --}}

    <h1>Live Payment</h1>

    <form action="{{ url('/phonepe/create') }}" method="post">
        <input type="text" name="refresh_token" value="">
        <input type="text" name="payer_name" value="Haresh">
        <input type="text" name="payer_email" value="hareshc1999@gmail.com">
        <input type="text" name="payer_mobile" value="9106029220">
        <input type="text" name="amount" value="1">
        <input type="text" name="order_id" value="{{ Str::random(10) }}">
        <input type="submit" value="Paynow">
    </form>
</body>
</html>
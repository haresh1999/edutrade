<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sabpaisa Payment Request</title>
</head>
<body>

    <h1>Sandbox Payment</h1>

    <form action="{{ url('/sabpaisa/sandbox/request') }}" method="post">
        <input type="text" name="client_id" value="edutrade">
        <input type="text" name="client_secret" value="1890b383-345a-42d3-88c7-6e80efd08460">
        <input type="text" name="payer_name" value="Haresh">
        <input type="text" name="payer_email" value="hareshc1999@gmail.com">
        <input type="text" name="payer_mobile" value="9106029220">
        <input type="text" name="amount" value="1">
        <input type="text" name="order_id" value="{{ Str::random(10) }}">
        <input type="submit" value="Paynow">
    </form>

    {{-- Live --}}

    <h1>Live Payment</h1>

    <form action="{{ url('/sabpaisa/request') }}" method="post">
        <input type="text" name="client_id" value="edutrade">
        <input type="text" name="client_secret" value="c73ba053-bf3f-4c9e-88ae-9e49fd4534e4">
        <input type="text" name="payer_name" value="Haresh">
        <input type="text" name="payer_email" value="hareshc1999@gmail.com">
        <input type="text" name="payer_mobile" value="9106029220">
        <input type="text" name="amount" value="1">
        <input type="text" name="order_id" value="{{ Str::random(10) }}">
        <input type="submit" value="Paynow">
    </form>
</body>
</html>
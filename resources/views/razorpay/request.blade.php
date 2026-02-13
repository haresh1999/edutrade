<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Edutrade - Payment Page</title>
</head>
<p>Please wait while we are opening the payment page.</p>

<body>
    <script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script type="text/javascript">
        $(function(){
        $.ajax({
            type: "POST",
            url: "{{ $input['order_id_url'] }}",
            data: { 
                id: "{{ $input['tnx_id'] }}",
                _token: "{{ csrf_token() }}" 
            },
            success: function(result) {
                var options = {
                        "key": "{{setting('key_id')}}",
                        "amount": "{{$input['amount']}}",
                        "currency": "INR",
                        "name": "Edutrade",
                        "description": "Order {{ $input['order_id'] }}",
                    
                        "image": "https://edutrade.in/wp-content/uploads/2025/09/cropped-cropped-Screenshot-2025-09-03-111318.png",
                        "order_id": result,
                        "callback_url": "{{ $input['callback_url'] }}",
                        "prefill": {
                            "name": "{{$input['payer_name']}}",
                            "email": "{{$input['payer_email']}}",
                            "contact": "{{$input['payer_mobile']}}"
                        },                       
                    };
                var rzp1 = new Razorpay(options);
                rzp1.open();  
            },
            error: function(result) {
                alert('Failed to create payment');
            }
        });
    })
    </script>
</body>

</html>
<?php

function setting($pg, $key)
{
    $env = config('services.env');

    return config("services.{$pg}.{$env}.{$key}");
}

function getPaymentGateway($amount)
{
    return match (true) {
        $amount >= 1000 && $amount < 3000 => 'phonepe',
        $amount >= 3000 && $amount < 5000 => 'paytm',
        $amount >= 5000 && $amount < 10000 => 'payu',
        $amount >= 10000 && $amount < 25000 => 'cashfree',
        $amount >= 25000 && $amount <= 50000 => 'razorpay',
        default => 'not_available',
    };
}

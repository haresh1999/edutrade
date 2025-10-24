<?php

function sabpaisa($key)
{
    return config("services.sabpaisa.production.{$key}");
}

function sabpaisaSandbox($key)
{
    return config("services.sabpaisa.sandbox.{$key}");
}

function razorpay($key)
{
    return config("services.razorpay.production.{$key}");
}

function razorpaySandbox($key)
{
    return config("services.razorpay.sandbox.{$key}");
}

<?php

function setting($key)
{
    if (str_contains(url()->current(), 'sabpaisa')) {

        if (str_contains(url()->current(), 'sandbox')) {

            return config("services.sabpaisa.sandbox.{$key}");
        }

        return config("services.sabpaisa.production.{$key}");
    }

    if (str_contains(url()->current(), 'razorpay')) {

        if (str_contains(url()->current(), 'sandbox')) {

            return config("services.razorpay.sandbox.{$key}");
        }

        return config("services.razorpay.production.{$key}");
    }

    if (str_contains(url()->current(), 'phonepe')) {

        if (str_contains(url()->current(), 'sandbox')) {

            return config("services.phonepe.sandbox.{$key}");
        }

        return config("services.phonepe.production.{$key}");
    }

    if (str_contains(url()->current(), 'paytm')) {

        if (str_contains(url()->current(), 'sandbox')) {

            return config("services.paytm.sandbox.{$key}");
        }

        return config("services.paytm.production.{$key}");
    }
}

<?php

function setting($pg, $key)
{
    $env = config('services.env');

    return config("services.{$pg}.{$env}.{$key}");
}

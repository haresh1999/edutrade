<?php

function sabpaisa($key)
{
    return config("services.sabpaisa.production.{$key}");
}

function sabpaisaSandbox($key)
{
    return config("services.sabpaisa.sandbox.{$key}");
}

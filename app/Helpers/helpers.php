<?php

function sabpaisa($key)
{
    return config("services.subpaisa.production.{$key}");
}

function sabpaisaSandbox($key)
{
    return config("services.subpaisa.sandbox.{$key}");
}

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'sabpaisa' => [
        'user' => [],
        'sandbox' => [
            'client_code' => 'DCRBP',
            'username' => 'userph.jha_3036',
            'password' => 'DBOI1_SP3036',
            'auth_key' => '0jeOYcu3UnfmWyLC',
            'auth_iv' => 'C28LAmGxXTqmK0QJ',
            'url' => 'https://stage-securepay.sabpaisa.in/SabPaisa/sabPaisaInit?v=1'
        ],
        'production' => [
            'client_code' => 'SHA9I6',
            'username' => 'ikondubai@gmail.com',
            'password' => 'SHA9I6_SP23560',
            'auth_key' => 'HT3sbrw8jrzKBmZqt0Wr7iFWZaq9mWy5j8d/Yu7WQxE=',
            'auth_iv' => '0Op4vCNftDe4i0OwdOfP99BW2qz8KCfOzyXz66VWL4L2q50/uzB2ygpF6Xq+2Vea',
            'url' => 'https://securepay.sabpaisa.in/SabPaisa/sabPaisaInit?v=1'
        ],
    ],

    'razorpay' => [
        'user' => [],
        'sandbox' => [
            'key_id' => 'rzp_test_RXE0uWen59xOZ0',
            'key_secret' => 'PRq5jSWVM7VvKc5fqEdaqhWn'
        ],
        'production' => [
            'key_id' => 'rzp_live_RTIzeZS7Q22shx',
            'key_secret' => 'nca3Agw8XlpW95Hfmp4et4al'
        ],
    ]
];

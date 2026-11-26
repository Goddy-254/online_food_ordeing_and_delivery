<?php
return [
    'env' => env('MPESA_ENV', 'sandbox'),
    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
    'shortcode' => env('MPESA_SHORTCODE'),
    'passkey' => env('MPESA_PASSKEY'),
    'callback_url' => env('MPESA_CALLBACK_URL'),
    'transaction_type' => env('MPESA_TRANSACTION_TYPE', 'CustomerPayBillOnline'),
    'base_url' => [
        'sandbox' => 'https://sandbox.safaricom.co.ke',
        'production' => 'https://api.safaricom.co.ke',
    ],
    'endpoints' => [
        'oauth' => '/oauth/v1/generate?grant_type=client_credentials',
        'stk_push' => '/mpesa/stkpush/v1/processrequest',
        'query' => '/mpesa/stkpushquery/v1/query',
    ],
];

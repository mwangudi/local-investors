<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Africa's Talking Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Africa's Talking SMS gateway.
    |
    */

    'username' => env('AT_USERNAME', 'sandbox'),
    'api_key' => env('AT_API_KEY', ''),
    'sender_id' => env('AT_SENDER_ID', ''),
    'environment' => env('AT_ENVIRONMENT', 'sandbox'),

    // API endpoints
    'endpoints' => [
        'sandbox' => 'https://api.sandbox.africastalking.com/version1/messaging',
        'production' => 'https://api.africastalking.com/version1/messaging',
    ],
];

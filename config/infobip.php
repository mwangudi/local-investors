<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Infobip SMS Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Infobip SMS gateway.
    |
    */

    'base_url' => env('INFOBIP_BASE_URL', ''),
    'api_key' => env('INFOBIP_API_KEY', ''),
    'sender_id' => env('INFOBIP_SENDER_ID', ''),
];

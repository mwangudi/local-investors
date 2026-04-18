<?php

return [
    /*
    | Channel toggles. When a flag is enabled, InAppNotification fan-outs
    | to that channel in addition to the in-app database record.
    */
    'email' => env('NOTIFY_EMAIL', false),
    'sms'   => env('NOTIFY_SMS', false),

    /*
    | SMS provider placeholder. Wire real credentials in .env.
    | Supported stubs: 'log' (writes to laravel.log), 'africastalking', 'twilio'.
    */
    'sms_driver'   => env('SMS_DRIVER', 'log'),
    'sms_from'     => env('SMS_FROM', 'LOCAL-INV'),
    'sms_username' => env('SMS_USERNAME'),
    'sms_api_key'  => env('SMS_API_KEY'),
];

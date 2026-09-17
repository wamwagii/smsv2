<?php

return [
    'default' => env('SMS_PROVIDER', 'africastalking'),

    'africastalking' => [
        'username' => trim((string) env('AFRICASTALKING_USERNAME', 'sandbox')),
        'api_key'  => trim((string) env('AFRICASTALKING_API_KEY')),
        'from'     => trim((string) env('AFRICASTALKING_FROM')) ?: null,
        'sandbox'  => (bool) env('AFRICASTALKING_SANDBOX', true),
    ],
];
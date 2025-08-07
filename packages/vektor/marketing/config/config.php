<?php

return [
    'enabled' => env('MARKETING_ENABLED', false),
    'mailchimp' => [
        'enabled' => env('MAILCHIMP_ENABLED', false),
        'api_key' => env('MAILCHIMP_API_KEY'),
        'server' => env('MAILCHIMP_SERVER'),
        'list_id' => env('MAILCHIMP_LIST_ID'),
    ],
];

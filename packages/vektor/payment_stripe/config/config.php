<?php

return [
    'enabled' => env('CHECKOUT_PAYMENT_STRIPE_ENABLED', false),
    'request' => [
        'enabled' => env('CHECKOUT_PAYMENT_STRIPE_REQUEST_ENABLED', false),
    ],
    'public_key' => env('STRIPE_KEY'),
    'secret_key' => env('STRIPE_SECRET'),
];

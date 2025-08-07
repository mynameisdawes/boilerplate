<?php

return [
    'enabled' => env('PASSWORDLESS_AUTH_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Email Sending
    |--------------------------------------------------------------------------
    |
    | Enable or disable email sending for passwordless authentication.
    | When disabled, no emails will be sent even when events are fired.
    |
    */
    'send_emails' => env('PASSWORDLESS_AUTH_SEND_EMAILS', true),

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | This is the model that should be used for authentication.
    |
    */
    'user_model' => env('PASSWORDLESS_AUTH_USER_MODEL', 'App\Models\User'),

    /*
    |--------------------------------------------------------------------------
    | Token Lifetime
    |--------------------------------------------------------------------------
    |
    | This value is the number of minutes that the passwordless login token
    | will be valid for. Defaults to 15 minutes.
    |
    */
    'token_lifetime' => env('PASSWORDLESS_AUTH_TOKEN_LIFETIME', 15),

    /*
    |--------------------------------------------------------------------------
    | Redirect After Login
    |--------------------------------------------------------------------------
    |
    | This is where users will be redirected after successful authentication.
    |
    */
    'redirect_to' => '/',

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for login attempts.
    |
    */
    'rate_limit' => [
        'enabled' => true,
        'attempts' => 5,
        'decay_minutes' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Options
    |--------------------------------------------------------------------------
    |
    | Additional security validations.
    |
    */
    'validate_ip' => env('PASSWORDLESS_AUTH_VALIDATE_IP', false),
    'validate_user_agent' => env('PASSWORDLESS_AUTH_VALIDATE_USER_AGENT', false),
];

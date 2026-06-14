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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'central' => [
        'api_key' => env('CENTRAL_API_KEY', 'KEY-MHS-50'),
        'api_key_local' => env('API_KEY', '102022400005'),
    ],

    'inventory' => [
        'mock' => env('MOCK_EXTERNAL_SERVICES', true),
        'url' => env('INVENTORY_SERVICE_URL', 'http://localhost:3001'),
        'key' => env('INVENTORY_SERVICE_KEY', 'dhika-nim-gudang-2026'),
    ],

];

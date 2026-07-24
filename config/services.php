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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
    ],

    'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY'),

    'armada' => [
        'mode' => env('ARMADA_MODE', 0), // 0 = test (v0), 1 = production (v1)
        'url' => env('ARMADA_URL', null), // If null, will use v0 for test or v1 for production based on mode
        'test_key' => env('ARMADA_TEST_KEY', ''), // Test key for mode 0
        'fallback_key' => env('ARMADA_ACCESS_TOKEN', ''), // Fallback if branch doesn't have armada_key in database (for production)
    ],

    'firebase' => [
        'credentials_path' => env('FIREBASE_CREDENTIALS_PATH', storage_path('app/firebase_credentials.json')),
    ],

];

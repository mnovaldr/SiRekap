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

    'supabase' => [
        'url' => env('SUPABASE_URL'),
        'key' => env('SUPABASE_KEY'),
    ],

    'mcc_sync' => [
        'url' => env('MCC_SYNC_URL', 'https://mcc.malangkota.go.id/jadwalku/'),
        'username' => env('MCC_SYNC_USERNAME'),
        'password' => env('MCC_SYNC_PASSWORD'),
    ],

    'ai_service' => [
        'predict_url' => env('AI_SERVICE_URL', 'http://127.0.0.1:8001/api/predict-sdgs'),
        'scrape_url' => env('AI_SCRAPE_URL', 'http://127.0.0.1:8001/api/scrape-mcc'),
        'internal_token' => env('AI_INTERNAL_TOKEN'),
    ],

];

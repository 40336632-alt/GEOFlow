<?php

return [

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

    'ai' => [
        'api_url' => env('AI_API_URL', 'https://api.minimax.chat/v1'),
        'api_key' => env('AI_API_KEY', ''),
        'model' => env('AI_MODEL', 'MiniMax-M2.7'),
    ],

    'autogeo' => [
        'url' => env('AUTOGEO_URL', 'http://localhost:5000'),
    ],

    'bitbrowser' => [
        'url' => env('BITBROWSER_URL', 'http://127.0.0.1:54345'),
    ],

    'toutiao_publisher' => [
        'http_host' => env('TOUTIAO_PUBLISHER_HTTP_HOST', '127.0.0.1'),
        'http_port' => env('TOUTIAO_PUBLISHER_HTTP_PORT', 18432),
    ],

];

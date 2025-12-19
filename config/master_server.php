<?php

/**
 * Master Server specific configuration
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Steam API Configuration
    |--------------------------------------------------------------------------
    */
    'steam' => [
        'api_key' => env('STEAM_API_KEY'),
        'api_url' => 'https://api.steampowered.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Server Authentication
    |--------------------------------------------------------------------------
    | Configuration for game server authentication using HMAC signatures
    */
    'server_auth' => [
        'secret' => env('SERVER_AUTH_SECRET'),
        'timestamp_tolerance' => (int) env('SERVER_AUTH_TIMESTAMP_TOLERANCE', 300),
        'algorithm' => 'sha256',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limits' => [
        'api' => (int) env('API_RATE_LIMIT_PER_MINUTE', 60),
        'server' => (int) env('SERVER_RATE_LIMIT_PER_MINUTE', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTL Settings (in seconds)
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'server_list_ttl' => 60,
        'server_details_ttl' => 30,
        'game_config_ttl' => 300,
        'instance_schema_ttl' => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | Server Heartbeat Configuration
    |--------------------------------------------------------------------------
    */
    'heartbeat' => [
        'interval' => 30,
        'timeout' => 90,
    ],
];

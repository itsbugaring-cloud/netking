<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'fonnte' => [
        'api_key' => env('FONNTE_API_KEY'),
        'base_url' => 'https://api.fonnte.com',
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'is_sanitized' => true,
        'is_3ds' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | MikroTik RouterOS API Configuration
    |--------------------------------------------------------------------------
    */

    'mikrotik' => [
        'host' => env('MIKROTIK_HOST'),
        'username' => env('MIKROTIK_USERNAME'),
        'password' => env('MIKROTIK_PASSWORD'),
        'port' => env('MIKROTIK_PORT', 8728),
    ],

    /*
    |--------------------------------------------------------------------------
    | ERKA51 Inventory API (internal proxy)
    |--------------------------------------------------------------------------
    */
    'inventory' => [
        'url'      => env('INVENTORY_API_URL', 'http://127.0.0.1:3000'),
        'username' => env('INVENTORY_SERVICE_USERNAME', ''),
        'password' => env('INVENTORY_SERVICE_PASSWORD', ''),
    ],

];

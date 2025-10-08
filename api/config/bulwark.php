<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sanctum Bulwark Enabled
    |--------------------------------------------------------------------------
    |
    | Determine if the Sanctum Bulwark should be enabled.
    |
     */
    'enabled' => env('BULWARK_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Bulwark Cache
    |--------------------------------------------------------------------------
    |
    | Sanctum Bulwark tokens cache store.
    |
     */
    'cache' =>  env('BULWARK_CACHE_STORE', config('cache.default')),

    /*
   |--------------------------------------------------------------------------
   | Sanctum Bulwark Queue
   |--------------------------------------------------------------------------
   |
   | Sanctum Bulwark queue connection and name for tokens updates.
   |
    */
    'queue' => [
        'connection' => env('BULWARK_QUEUE_CONNECTION', config('queue.default')),
        'name' => env('BULWARK_QUEUE_NAME', 'bulwark')
    ],
];

<?php

return [
    'eloquent' => [
        'limits' => tap(array_values(array_filter(
            array_map(
                'intval',
                explode(',', env('REPOSITORY_ELOQUENT_LIMITS', '20,30,50,100'))
            ),
            fn ($value) => $value > 0 && $value <= 100
        )), function (&$array) {
            sort($array);
        }),
        'cache' => [
            'store' => env('REPOSITORY_CACHE_STORE'),
            'ttl' => env('REPOSITORY_CACHE_TTL', 24*60*60),
        ],
    ],
];
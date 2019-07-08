<?php

declare(strict_types=1);

return [

    /*
     * Available drivers: "mock", "google"
     */
    'driver' => env('GEOCODE_DRIVER', 'mock'),

    'drivers' => [

        'google' => [
            'api_key' => env('GOOGLE_API_KEY'),
        ],

    ],

];

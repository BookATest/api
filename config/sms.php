<?php

declare(strict_types=1);

return [

    /*
     * Available drivers: "log", "twilio"
     */
    'driver' => env('SMS_DRIVER', 'log'),

    'queue' => env('SMS_QUEUE', 'notifications'),

    'drivers' => [

        'twilio' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_TOKEN'),
            'from' => env('TWILIO_FROM', 'Book A Test'),
        ],

    ],

];

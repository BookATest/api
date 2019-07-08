<?php

declare(strict_types=1);

use Faker\Generator as Faker;

$factory->define(App\Models\Audit::class, function (Faker $faker) {
    return [
        'action' => \App\Models\Audit::READ,
        'ip_address' => $faker->ipv4,
        'user_agent' => $faker->userAgent,
    ];
});

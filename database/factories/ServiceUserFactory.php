<?php

declare(strict_types=1);

use App\Models\ServiceUser;
use Faker\Generator as Faker;

$factory->define(ServiceUser::class, function (Faker $faker) {
    $faker->addProvider(new \App\Faker\BatProvider($faker));

    return [
        'name' => $faker->name,
        'phone' => $faker->ukMobileNumber,
        'preferred_contact_method' => ServiceUser::PHONE,
    ];
});

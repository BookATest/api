<?php

use App\Models\ServiceUser;
use Faker\Generator as Faker;

$factory->define(ServiceUser::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'phone' => $faker->phoneNumber,
        'preferred_contact_method' => ServiceUser::PHONE,
    ];
});

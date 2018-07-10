<?php

use App\Models\ServiceUser;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(ServiceUser::class, function (Faker $faker) {
    return [
        'uuid' => Str::uuid(),
        'name' => $faker->name,
        'phone' => $faker->phoneNumber,
        'preferred_contact_method' => ServiceUser::PHONE,
    ];
});

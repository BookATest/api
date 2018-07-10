<?php

use App\Models\User;
use Faker\Generator as Faker;

$factory->define(User::class, function (Faker $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'phone' => $faker->phoneNumber,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'display_email' => rand(0, 1),
        'display_phone' => rand(0, 1),
        'include_calendar_attachment' => rand(0, 1),
        'calendar_feed_token' => str_random(10),
        'remember_token' => str_random(10),
    ];
});

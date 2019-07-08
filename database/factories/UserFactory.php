<?php

declare(strict_types=1);

use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(User::class, function (Faker $faker) {
    $faker->addProvider(new \App\Faker\BatProvider($faker));

    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'phone' => $faker->ukMobileNumber,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'display_email' => mt_rand(0, 1),
        'display_phone' => mt_rand(0, 1),
        'receive_booking_confirmations' => true,
        'receive_cancellation_confirmations' => true,
        'include_calendar_attachment' => mt_rand(0, 1),
        'calendar_feed_token' => User::generateCalendarFeedToken(),
        'remember_token' => Str::random(10),
    ];
});

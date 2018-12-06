<?php

use App\Models\Clinic;
use Faker\Generator as Faker;

$factory->define(Clinic::class, function (Faker $faker) {
    $faker->addProvider(new \App\Faker\BatProvider($faker));

    return [
        'name' => $faker->unique()->company,
        'phone' => $faker->ukMobileNumber,
        'email' => $faker->companyEmail,
        'address_line_1' => mt_rand(1, 100) . ' ' . $faker->streetName,
        'city' => $faker->city,
        'postcode' => $faker->postcode,
        'directions' => $faker->sentence,
        'appointment_duration' => 30,
        'appointment_booking_threshold' => 120,
        'send_cancellation_confirmations' => true,
    ];
});

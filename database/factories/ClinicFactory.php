<?php

use App\Models\Clinic;
use Faker\Generator as Faker;

$factory->define(Clinic::class, function (Faker $faker) {
    return [
        'name' => $faker->company,
        'phone' => $faker->phoneNumber,
        'email' => $faker->companyEmail,
        'address_line_1' => $faker->streetAddress,
        'city' => $faker->city,
        'postcode' => $faker->postcode,
        'directions' => 'Go straight past the town hall and it is on the left',
        'appointment_duration' => 30,
        'appointment_booking_threshold' => 120,
    ];
});

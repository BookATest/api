<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Appointment::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory(\App\Models\User::class)->create()->id;
        },
        'clinic_id' => function () {
            return factory(\App\Models\Clinic::class)->create()->id;
        },
        'start_at' => \Illuminate\Support\Carbon::now()->setTime(15, 0),
    ];
});

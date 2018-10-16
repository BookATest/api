<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Report::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory(\App\Models\User::class)->create()->id;
        },
        'file_id' => function () {
            return factory(\App\Models\File::class)->create()->id;
        },
        'report_type_id' => function () {
            return \App\Models\ReportType::query()->firstOrFail()->id;
        },
        'start_at' => today()->startOfMonth(),
        'end_at' => today()->endOfMonth(),
    ];
});

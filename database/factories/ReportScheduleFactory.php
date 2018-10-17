<?php

use Faker\Generator as Faker;

$factory->define(App\Models\ReportSchedule::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory(\App\Models\User::class)->create()->id;
        },
        'report_type_id' => function () {
            return \App\Models\ReportType::query()->firstOrFail()->id;
        },
        'repeat_type' => \App\Models\ReportSchedule::WEEKLY,
    ];
});

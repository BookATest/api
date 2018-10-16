<?php

use Faker\Generator as Faker;

$factory->define(App\Models\File::class, function (Faker $faker) {
    return [
        'filename' => str_slug($faker->sentence(3), '_') . '.txt',
        'mime_type' => 'text/plain',
    ];
});

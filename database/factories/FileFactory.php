<?php

declare(strict_types=1);

use App\Models\File;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(File::class, function (Faker $faker) {
    return [
        'filename' => Str::slug($faker->sentence(3), '_') . '.txt',
        'mime_type' => 'text/plain',
    ];
});

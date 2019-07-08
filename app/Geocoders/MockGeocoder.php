<?php

declare(strict_types=1);

namespace App\Geocoders;

use App\Contracts\Geocoder;
use App\Support\Coordinate;
use App\Support\Postcode;
use Faker\Factory;

class MockGeocoder implements Geocoder
{
    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * MockGeocoder constructor.
     */
    public function __construct()
    {
        $this->faker = Factory::create(config('app.faker_locale') ?? Factory::DEFAULT_LOCALE);
    }

    /**
     * @param \App\Support\Postcode $postcode
     * @return \App\Support\Coordinate
     */
    public function geocode(Postcode $postcode): Coordinate
    {
        return new Coordinate($this->faker->latitude, $this->faker->longitude);
    }
}

<?php

declare(strict_types=1);

namespace App\Geocoders;

use App\Contracts\Geocoder;
use App\Support\Coordinate;
use App\Support\Postcode;

class StubGeocoder implements Geocoder
{
    // Coordinates for Ayup Digital's office.
    const LATITUDE = 53.795708;
    const LONGITUDE = -1.550738;

    /**
     * @param \App\Support\Postcode $postcode
     * @return \App\Support\Coordinate
     */
    public function geocode(Postcode $postcode): Coordinate
    {
        return new Coordinate(static::LATITUDE, static::LONGITUDE);
    }
}

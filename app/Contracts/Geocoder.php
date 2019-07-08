<?php

namespace App\Contracts;

use App\Support\Coordinate;
use App\Support\Postcode;

interface Geocoder
{
    /**
     * @param \App\Support\Postcode $postcode
     * @return \App\Support\Coordinate
     */
    public function geocode(Postcode $postcode): Coordinate;
}

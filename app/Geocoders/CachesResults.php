<?php

declare(strict_types=1);

namespace App\Geocoders;

use App\Models\CachedGeocodeResult;
use App\Support\Coordinate;
use App\Support\Postcode;

trait CachesResults
{
    /**
     * @param \App\Support\Postcode $postcode
     * @return string
     */
    protected function normaliseAddress(Postcode $postcode): string
    {
        $postcode = mb_strtolower($postcode->get());
        $postcode = single_space($postcode);

        return "$postcode, united kingdom";
    }

    /**
     * @param \App\Support\Postcode $postcode
     * @return \App\Support\Coordinate|null
     */
    protected function fetchCached(Postcode $postcode): ?Coordinate
    {
        /** @var \App\Models\CachedGeocodeResult|null $cachedGeocodeResult */
        $cachedGeocodeResult = CachedGeocodeResult::where('query', $this->normaliseAddress($postcode))->first();

        if ($cachedGeocodeResult === null || $cachedGeocodeResult->toCoordinate() === null) {
            return null;
        }

        return $cachedGeocodeResult->toCoordinate();
    }

    /**
     * @param \App\Support\Postcode $postcode
     * @param \App\Support\Coordinate|null $coordinate
     * @return \App\Models\CachedGeocodeResult
     */
    protected function saveToCache(Postcode $postcode, ?Coordinate $coordinate): CachedGeocodeResult
    {
        return CachedGeocodeResult::updateOrCreate([
            'query' => $this->normaliseAddress($postcode),
        ], [
            'lat' => optional($coordinate)->getLatitude(),
            'lon' => optional($coordinate)->getLongitude(),
        ]);
    }
}

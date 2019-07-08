<?php

declare(strict_types=1);

namespace App\Geocoders;

use App\Contracts\Geocoder;
use App\Exceptions\AddressNotFoundException;
use App\Support\Coordinate;
use App\Support\Postcode;
use GuzzleHttp\Client;

class GoogleGeocoder implements Geocoder
{
    use CachesResults;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * GoogleGeocoder constructor.
     */
    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://maps.googleapis.com/']);
        $this->apiKey = config('geocode.drivers.google.api_key');
    }

    /**
     * @param \App\Support\Postcode $postcode
     * @throws \App\Exceptions\AddressNotFoundException
     * @return \App\Support\Coordinate
     */
    public function geocode(Postcode $postcode): Coordinate
    {
        // First attempt to retrieve the coordinate from the cache.
        $cachedGeocodeResult = $this->fetchCached($postcode);

        if ($cachedGeocodeResult !== null) {
            return $cachedGeocodeResult;
        }

        // Fetch from Google Geocode API.
        return $this->fetchFromGoogle($postcode);
    }

    /**
     * @param \App\Support\Postcode $postcode
     * @throws \App\Exceptions\AddressNotFoundException
     * @return \App\Support\Coordinate
     */
    protected function fetchFromGoogle(Postcode $postcode): Coordinate
    {
        // Make the request.
        $response = $this->client->get('/maps/api/geocode/json', [
            'query' => [
                'address' => $this->normaliseAddress($postcode),
                'key' => $this->apiKey,
            ],
        ]);

        // Parse the results.
        $json = json_decode($response->getBody()->getContents(), true);
        $results = $json['results'];

        // Throw an exception if no address was found.
        if (count($results) === 0) {
            $this->saveToCache($postcode, null);

            throw new AddressNotFoundException();
        }

        // Get the latitude and longitude.
        $location = $results[0]['geometry']['location'];
        $coordinate = new Coordinate($location['lat'], $location['lng']);

        // Save to cache.
        $this->saveToCache($postcode, $coordinate);

        return $coordinate;
    }
}

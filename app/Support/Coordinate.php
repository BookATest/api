<?php

namespace App\Support;

use InvalidArgumentException;

class Coordinate
{
    /**
     * @var float
     */
    protected $lat;

    /**
     * @var float
     */
    protected $lon;

    /**
     * Coordinate constructor.
     *
     * @param float $lat
     * @param float $lon
     */
    public function __construct(float $lat, float $lon)
    {
        $this->set($lat, $lon);
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return [
            'lat' => $this->lat,
            'lon' => $this->lon,
        ];
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->lat;
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->lon;
    }

    /**
     * @param float $lat
     * @param float $lon
     * @return \App\Support\Coordinate
     */
    public function set(float $lat, float $lon): self
    {
        if (!static::validate($lat, $lon)) {
            throw new InvalidArgumentException('The coordinate is invalid');
        }

        $this->lat = $lat;
        $this->lon = $lon;

        return $this;
    }

    /**
     * @param float $lat
     * @param float $lon
     * @return bool
     */
    public static function validate(float $lat, float $lon): bool
    {
        if ($lat < -90 || $lat > 90) {
            return false;
        }
        if ($lon < -180 || $lon > 180) {
            return false;
        }

        return true;
    }
}

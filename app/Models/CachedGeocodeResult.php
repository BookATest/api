<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Mutators\CachedGeocodeResultMutators;
use App\Models\Relationships\CachedGeocodeResultRelationships;
use App\Support\Coordinate;

class CachedGeocodeResult extends Model
{
    use CachedGeocodeResultMutators;
    use CachedGeocodeResultRelationships;

    /**
     * @return \App\Support\Coordinate|null
     */
    public function toCoordinate(): ?Coordinate
    {
        if ($this->lat === null || $this->lon === null) {
            return null;
        }

        return new Coordinate($this->lat, $this->lon);
    }
}

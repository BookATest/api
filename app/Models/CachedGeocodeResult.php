<?php

namespace App\Models;

use App\Models\Mutators\CachedGeocodeResultMutators;
use App\Models\Relationships\CachedGeocodeResultRelationships;

class CachedGeocodeResult extends Model
{
    use CachedGeocodeResultMutators;
    use CachedGeocodeResultRelationships;
}

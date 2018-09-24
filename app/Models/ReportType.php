<?php

namespace App\Models;

use App\Models\Mutators\ReportTypeMutators;
use App\Models\Relationships\ReportTypeRelationships;

class ReportType extends Model
{
    use ReportTypeMutators;
    use ReportTypeRelationships;

    const COUNT_APPOINTMENTS_AVAILABLE = 'count_appointments_available';
    const COUNT_APPOINTMENTS_BOOKED = 'count_appointments_booked';
    const COUNT_DID_NOT_ATTEND = 'count_did_not_attend';
    const COUNT_TESTING_TYPES = 'count_testing_types';

    /**
     * @param string $name
     * @return int
     */
    public static function getIdFor(string $name): int
    {
        return static::where('name', $name)->firstOrFail()->id;
    }
}

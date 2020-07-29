<?php

namespace App\Models\Mutators;

trait ClinicMutators
{
    /**
     * @return int
     */
    public function getSlotsAttribute(): int
    {
        $minutesInDay = 24 * 60;

        return $minutesInDay / $this->appointment_duration;
    }

    /*
     * Language.
     */

    public function getLanguageAttribute(string $value)
    {
        return json_decode($value, true);
    }

    public function setLanguageAttribute($value)
    {
        $this->attributes['language'] = json_encode($value);
    }
}

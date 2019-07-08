<?php

declare(strict_types=1);

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
}

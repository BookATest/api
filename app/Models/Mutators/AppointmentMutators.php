<?php

namespace App\Models\Mutators;

trait AppointmentMutators
{
    /**
     * @return bool
     */
    public function getIsBookedAttribute(): bool
    {
        return $this->service_user_id !== null;
    }
}

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

    /**
     * @return null|string
     */
    public function getServiceUserNameAttribute(): ?string
    {
        return $this->serviceUser->name ?? null;
    }

    /**
     * @return string
     */
    public function getUserFirstNameAttribute(): string
    {
        return $this->user->first_name;
    }

    /**
     * @return string
     */
    public function getUserLastNameAttribute(): string
    {
        return $this->user->last_name;
    }

    /**
     * @return null|string
     */
    public function getUserEmailAttribute(): ?string
    {
        return $this->user->display_email ? $this->user->email : null;
    }

    /**
     * @return null|string
     */
    public function getUserPhoneAttribute(): ?string
    {
        return $this->user->display_phone ? $this->user->phone : null;
    }
}

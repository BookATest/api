<?php

namespace App\Models\Mutators;

use Carbon\CarbonImmutable;

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

    /**
     * @param string $startAt
     * @return \Carbon\CarbonImmutable
     * @throws \Exception
     */
    public function getStartAtAttribute(string $startAt): CarbonImmutable
    {
        // Convert UTC time stored in database to application timezone.
        return (new CarbonImmutable($startAt, 'UTC'))->timezone(config('app.timezone'));
    }

    /**
     * @param \Carbon\CarbonImmutable $startAt
     */
    public function setStartAtAttribute(CarbonImmutable $startAt)
    {
        // Convert from application timezone to UTC before storing in database.
        $this->attributes['start_at'] = $startAt->timezone('UTC')->toDateTimeString();
    }
}

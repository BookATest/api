<?php

namespace App\Models\Mutators;

use Illuminate\Support\Carbon;

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
     * @return \Illuminate\Support\Carbon
     * @throws \Exception
     */
    public function getStartAtAttribute(string $startAt): Carbon
    {
        // Convert UTC time stored in database to application timezone.
        return (new Carbon($startAt, 'UTC'))->timezone(config('app.timezone'));
    }

    /**
     * @param \Illuminate\Support\Carbon $startAt
     */
    public function setStartAtAttribute(Carbon $startAt)
    {
        // Convert from application timezone to UTC before storing in database.
        $this->attributes['start_at'] = $startAt->timezone('UTC')->toDateTimeString();
    }
}

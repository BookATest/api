<?php

namespace App\Models\Mutators;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;

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
     * @return string|null
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
     * @return string|null
     */
    public function getUserEmailAttribute(): ?string
    {
        return $this->user->display_email ? $this->user->email : null;
    }

    /**
     * @return string|null
     */
    public function getUserPhoneAttribute(): ?string
    {
        return $this->user->display_phone ? $this->user->phone : null;
    }

    /**
     * @param string $startAt
     * @throws \Exception
     * @return \Carbon\CarbonInterface
     */
    public function getStartAtAttribute(string $startAt): CarbonInterface
    {
        // Convert UTC time stored in database to application timezone.
        return Date::createFromFormat('Y-m-d H:i:s', $startAt, 'UTC')
            ->timezone(config('app.timezone'));
    }

    /**
     * @param \Carbon\CarbonInterface $startAt
     */
    public function setStartAtAttribute(CarbonInterface $startAt)
    {
        // Convert from application timezone to UTC before storing in database.
        $this->attributes['start_at'] = $startAt
            ->timezone(config('app.timezone')) // Done to prevent invalid dates.
            ->timezone('UTC')
            ->toDateTimeString();
    }
}

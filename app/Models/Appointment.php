<?php

namespace App\Models;

use App\Models\Mutators\AppointmentMutators;
use App\Models\Relationships\AppointmentRelationships;
use App\Models\Scopes\AppointmentScopes;
use Illuminate\Support\Carbon;

class Appointment extends Model
{
    use AppointmentMutators;
    use AppointmentRelationships;
    use AppointmentScopes;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'did_not_attend' => 'boolean',
        'start_at' => 'datetime',
        'booked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * @return bool
     */
    public function hasSchedule(): bool
    {
        return $this->appointment_schedule_id !== null;
    }

    /**
     * @param \App\Models\ServiceUser $serviceUser
     * @param \Illuminate\Support\Carbon|null $bookedAt
     * @return \App\Models\Appointment
     */
    public function book(ServiceUser $serviceUser, Carbon $bookedAt = null): self
    {
        $bookedAt = $bookedAt ?? now();

        $this->update([
            'service_user_id' => $serviceUser->id,
            'booked_at' => $bookedAt,
        ]);

        return $this;
    }

    /**
     * @return \App\Models\Appointment
     */
    public function cancel(): self
    {
        $this->update([
            'service_user_id' => null,
            'booked_at' => null,
        ]);

        return $this;
    }
}

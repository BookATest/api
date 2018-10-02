<?php

namespace App\Rules;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

class AppointmentIsWithinSlot implements Rule
{
    /**
     * @var \App\Models\User
     */
    protected $user;

    /**
     * @var \App\Models\Clinic|clinic
     */
    protected $clinic;

    /**
     * Create a new rule instance.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Clinic|null $clinic
     */
    public function __construct(User $user, ?Clinic $clinic)
    {
        $this->user = $user;
        $this->clinic = $clinic;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $startAt
     * @return bool
     */
    public function passes($attribute, $startAt)
    {
        if ($this->clinic === null) {
            return false;
        }

        if (!is_string($startAt)) {
            return false;
        }

        if (Carbon::hasFormat($startAt, Carbon::ISO8601)) {
            return false;
        }

        $startAt = Carbon::createFromFormat(Carbon::ISO8601, $startAt)->second(0);
        $totalMinutes = $startAt->copy()->startOfDay()->diffInMinutes($startAt);
        $isInSlot = ($totalMinutes % $this->clinic->appointment_duration) === 0;

        return $isInSlot;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute field does not fall within a slot for this clinic.';
    }
}

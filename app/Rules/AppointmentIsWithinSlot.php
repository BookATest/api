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
     * @var \App\Models\Clinic
     */
    protected $clinic;

    /**
     * @var \Illuminate\Support\Carbon
     */
    protected $startAt;

    /**
     * Create a new rule instance.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Clinic $clinic
     * @param \Illuminate\Support\Carbon $startAt
     */
    public function __construct(User $user, Clinic $clinic, Carbon $startAt)
    {
        $this->user = $user;
        $this->clinic = $clinic;
        $this->startAt = $startAt;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $totalMinutes = $this->startAt->copy()->startOfDay()->diffInMinutes($this->startAt);
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

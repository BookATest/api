<?php

namespace App\Rules;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

class AppointmentDoesntOverlap implements Rule
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
        return Appointment::query()
            ->where('user_id',$this->user->id)
            ->where('clinic_id', $this->clinic->id)
            ->where('start_at', $this->startAt)
            ->doesntExist();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute field overlaps with an existing appointment.';
    }
}

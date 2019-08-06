<?php

namespace App\Rules;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;

class AppointmentDoesntOverlap implements Rule
{
    /**
     * @var \App\Models\User
     */
    protected $user;

    /**
     * @var \App\Models\Clinic|null
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
     * @param string $attribute
     * @param mixed $startAt
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

        try {
            /** @var \Carbon\CarbonImmutable $startAt */
            $startAt = Date::createFromFormat(CarbonImmutable::ATOM, $startAt);
            $startAt = $startAt->second(0);
        } catch (InvalidArgumentException $exception) {
            return false;
        }

        // TODO: Use appointment durations to ensure no overlap.
        return Appointment::query()
            ->where('user_id', $this->user->id)
            ->where('clinic_id', $this->clinic->id)
            ->where('start_at', $startAt->timezone('UTC'))
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

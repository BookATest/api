<?php

namespace App\Rules;

use App\Models\Appointment;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Date;

class AppointmentAvailable implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $appointmentId
     * @return bool
     */
    public function passes($attribute, $appointmentId)
    {
        if (!is_string($appointmentId)) {
            return false;
        }

        $appointment = Appointment::find($appointmentId);

        if ($appointment === null) {
            return false;
        }

        if ($appointment->is_booked) {
            return false;
        }

        $latestBookingTime = $appointment->start_at->subMinutes(
            $appointment->clinic->appointment_booking_threshold
        );

        if (Date::now()->greaterThan($latestBookingTime)) {
            return false;
        }

        if (!$appointment->clinic->hasEligibleAnswers()) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'This clinic has not yet updated their eligible answers.';
    }
}

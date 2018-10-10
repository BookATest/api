<?php

namespace App\Rules;

use App\Models\Appointment;
use Illuminate\Contracts\Validation\Rule;

class AppointmentAvailable implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $appointmentId
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

        if (now()->greaterThan($latestBookingTime)) {
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
        return 'The validation error message.';
    }
}

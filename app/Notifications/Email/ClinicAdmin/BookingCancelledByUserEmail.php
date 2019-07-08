<?php

namespace App\Notifications\Email\ClinicAdmin;

use App\Models\Appointment;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\Email\Email;

class BookingCancelledByUserEmail extends Email
{
    /**
     * BookingCancelledByUserEmail constructor.
     *
     * @param \App\Models\Appointment $appointment
     * @param \App\Models\User $user The clinic admin to send this email to
     */
    public function __construct(Appointment $appointment, User $user)
    {
        parent::__construct();

        $this->to = $user->email;
        $this->subject = 'Booking Cancellation';
        $this->message = <<<EOT
            An appointment has been cancelled with {$appointment->clinic->name} at {$appointment->start_at->format('l jS F H:i')}.
            
            The appointment was booked with {$appointment->user->full_name}.
            EOT;

        $this->notification = $user->notifications()->create([
            'channel' => Notification::EMAIL,
            'recipient' => $user->email,
            'message' => $this->message,
        ]);
    }
}

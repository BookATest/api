<?php

namespace App\Notifications\Email\ServiceUser;

use App\Models\Appointment;
use App\Models\Notification;
use App\Notifications\Email\Email;

class BookingConfirmedEmail extends Email
{
    /**
     * BookingConfirmedEmail constructor.
     *
     * @param \App\Models\Appointment $appointment
     */
    public function __construct(Appointment $appointment)
    {
        parent::__construct();

        $this->to = $appointment->serviceUser->email;
        $this->subject = 'Booking Confirmation';
        $this->message = "Your appointment has been booked with {$appointment->clinic->name} at {$appointment->start_at->format('')}.";
        $this->notification = $appointment->serviceUser->notifications()->create([
            'channel' => Notification::EMAIL,
            'recipient' => $appointment->serviceUser->email,
            'message' => $this->message,
        ]);
    }
}

<?php

namespace App\Notifications\Email\CommunityWorker;

use App\Models\Appointment;
use App\Models\Notification;
use App\Notifications\Email\Email;

class BookingCancelledByUserEmail extends Email
{
    /**
     * BookingCancelledByUserEmail constructor.
     *
     * @param \App\Models\Appointment $appointment
     */
    public function __construct(Appointment $appointment)
    {
        parent::__construct();

        $this->to = $appointment->user->email;
        $this->subject = 'Booking Cancellation';
        $this->message = "An appointment has been cancelled with {$appointment->clinic->name} at {$appointment->start_at->format('l jS F H:i')}.";
        $this->notification = $appointment->user->notifications()->create([
            'channel' => Notification::EMAIL,
            'recipient' => $appointment->user->email,
            'message' => $this->message,
        ]);
    }
}

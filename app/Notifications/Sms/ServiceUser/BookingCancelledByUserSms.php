<?php

namespace App\Notifications\Sms\ServiceUser;

use App\Models\Appointment;
use App\Models\Notification;
use App\Notifications\Sms\Sms;

class BookingCancelledByUserSms extends Sms
{
    /**
     * BookingCancelledByUserSms constructor.
     *
     * @param \App\Models\Appointment $appointment
     */
    public function __construct(Appointment $appointment)
    {
        parent::__construct();

        $this->to = $appointment->serviceUser->phone;
        $this->message = "Your appointment has been cancelled at {$appointment->start_at->format('l jS F H:i')} by an admin.";
        $this->notification = $appointment->serviceUser->notifications()->create([
            'channel' => Notification::SMS,
            'recipient' => $appointment->serviceUser->phone,
            'message' => $this->message,
        ]);
    }
}

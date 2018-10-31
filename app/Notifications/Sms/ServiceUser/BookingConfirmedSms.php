<?php

namespace App\Notifications\Sms\ServiceUser;

use App\Models\Appointment;
use App\Models\Notification;
use App\Notifications\Sms\Sms;

class BookingConfirmedSms extends Sms
{
    /**
     * BookingConfirmedSms constructor.
     *
     * @param \App\Models\Appointment $appointment
     */
    public function __construct(Appointment $appointment)
    {
        parent::__construct();

        $this->to = $appointment->serviceUser->phone;
        $this->message = "Your appointment has been booked with {$appointment->clinic->name} at {$appointment->start_at->format('H:i')}.";
        $this->notification = $appointment->serviceUser->notifications()->create([
            'channel' => Notification::SMS,
            'recipient' => $appointment->serviceUser->phone,
            'message' => $this->message,
        ]);
    }
}

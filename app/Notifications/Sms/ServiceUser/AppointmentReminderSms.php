<?php

namespace App\Notifications\Sms\ServiceUser;

use App\Models\Appointment;
use App\Models\Notification;
use App\Notifications\Sms\Sms;

class AppointmentReminderSms extends Sms
{
    /**
     * AccessCodeSms constructor.
     *
     * @param \App\Models\Appointment $appointment
     */
    public function __construct(Appointment $appointment)
    {
        parent::__construct();

        $this->to = $appointment->serviceUser->phone;
        $this->message = "Reminder that you have an appointment with {$appointment->clinic->name} at {$appointment->start_at->format('H:i')} today.";
        $this->notification = $appointment->serviceUser->notifications()->create([
            'channel' => Notification::SMS,
            'recipient' => $appointment->serviceUser->phone,
            'message' => $this->message,
        ]);
    }
}

<?php

namespace App\Notifications\Email\ServiceUser;

use App\Models\Appointment;
use App\Models\Notification;
use App\Models\Setting;
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

        $organisationName = Setting::getValue(Setting::NAME);

        $this->to = $appointment->serviceUser->email;
        $this->subject = 'Booking Confirmation';
        $this->message = "Your appointment has been booked for {$appointment->start_at->format('l jS F H:i')} with {$organisationName}.";
        $this->notification = $appointment->serviceUser->notifications()->create([
            'channel' => Notification::EMAIL,
            'recipient' => $appointment->serviceUser->email,
            'message' => $this->message,
        ]);
    }
}

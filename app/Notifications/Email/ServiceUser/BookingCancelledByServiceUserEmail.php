<?php

declare(strict_types=1);

namespace App\Notifications\Email\ServiceUser;

use App\Models\Appointment;
use App\Models\Notification;
use App\Models\Setting;
use App\Notifications\Email\Email;

class BookingCancelledByServiceUserEmail extends Email
{
    /**
     * BookingCancelledByServiceUserEmail constructor.
     *
     * @param \App\Models\Appointment $appointment
     */
    public function __construct(Appointment $appointment)
    {
        parent::__construct();

        $organisationName = Setting::getValue(Setting::NAME);

        $this->to = $appointment->serviceUser->email;
        $this->subject = 'Booking Cancellation';
        $this->message = "Your appointment for {$appointment->start_at->format('l jS F H:i')} with {$organisationName} has been cancelled.";
        $this->notification = $appointment->serviceUser->notifications()->create([
            'channel' => Notification::EMAIL,
            'recipient' => $appointment->serviceUser->email,
            'message' => $this->message,
        ]);
    }
}

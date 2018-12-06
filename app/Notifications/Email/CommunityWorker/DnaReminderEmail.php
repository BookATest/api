<?php

namespace App\Notifications\Email\CommunityWorker;

use App\Models\Appointment;
use App\Models\Notification;
use App\Notifications\Email\Email;

class DnaReminderEmail extends Email
{
    /**
     * BookingConfirmedEmail constructor.
     *
     * @param \App\Models\Appointment $appointment
     */
    public function __construct(Appointment $appointment)
    {
        parent::__construct();

        $yesUrl = backend_uri("/appointments/{$appointment->id}/edit?did_not_attend=false");
        $noUrl = backend_uri("/appointments/{$appointment->id}/edit?did_not_attend=true");

        $this->to = $appointment->user->email;
        $this->subject = 'Did They Attend?';
        $this->message = <<<EOT
Did {$appointment->serviceUser->name} attend their recent appointment with {$appointment->clinic->name} 
at {$appointment->start_at->format('H:i')}?

Yes: {$yesUrl}
No: {$noUrl}
EOT;
        $this->notification = $appointment->user->notifications()->create([
            'channel' => Notification::EMAIL,
            'recipient' => $appointment->user->email,
            'message' => $this->message,
        ]);
    }
}
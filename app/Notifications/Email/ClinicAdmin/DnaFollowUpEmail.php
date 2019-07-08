<?php

namespace App\Notifications\Email\ClinicAdmin;

use App\Models\Appointment;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\Email\Email;

class DnaFollowUpEmail extends Email
{
    /**
     * BookingConfirmedEmail constructor.
     *
     * @param \App\Models\Appointment $appointment
     * @param \App\Models\User $user
     */
    public function __construct(Appointment $appointment, User $user)
    {
        parent::__construct();

        $this->to = $user->email;
        $this->subject = 'DNA Needs Actioning';
        $this->message = <<<EOT
The DNA status has not yet been actioned by {$appointment->user->full_name} for their appointment with
{$appointment->clinic->name} at {$appointment->start_at->format('l jS F H:i')} for {$appointment->serviceUser->name}.
EOT;
        $this->notification = $user->notifications()->create([
            'channel' => Notification::EMAIL,
            'recipient' => $user->email,
            'message' => $this->message,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Notifications\Email\User;

use App\Models\Notification;
use App\Models\User;
use App\Notifications\Email\Email;

class ForgottenPasswordEmail extends Email
{
    /**
     * ForgottenPasswordEmail constructor.
     *
     * @param \App\Models\User $user
     * @param string $token
     */
    public function __construct(User $user, string $token)
    {
        parent::__construct();

        $this->to = $user->email;
        $this->subject = 'Forgotten Password';
        $this->message = 'Click here to reset your password ' . route('password.reset', ['token' => $token]);
        $this->notification = $user->notifications()->create([
            'channel' => Notification::EMAIL,
            'recipient' => $user->email,
            'message' => $this->message,
        ]);
    }
}

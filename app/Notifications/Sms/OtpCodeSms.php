<?php

namespace App\Notifications\Sms;

use App\Models\Notification;
use App\Models\User;

class OtpCodeSms extends Sms
{
    /**
     * OtpCodeSms constructor.
     *
     * @param \App\Models\User $user
     * @param string $otpCode
     */
    public function __construct(User $user, string $otpCode)
    {
        parent::__construct();

        $this->to = $user->phone;
        $this->message = "Your verification code is {$otpCode}";
        $this->notification = $user->notifications()->create([
            'channel' => Notification::SMS,
            'recipient' => $user->phone,
            'message' => $this->message,
        ]);
    }
}

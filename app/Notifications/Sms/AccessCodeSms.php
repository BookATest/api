<?php

namespace App\Notifications\Sms;

use App\Models\Notification;
use App\Models\ServiceUser;

class AccessCodeSms extends Sms
{
    /**
     * AccessCodeSms constructor.
     *
     * @param \App\Models\ServiceUser $serviceUser
     * @param string $accessCode
     */
    public function __construct(ServiceUser $serviceUser, string $accessCode)
    {
        parent::__construct();

        $this->to = $serviceUser->phone;
        $this->message = "Your verification code is {$accessCode}";
        $this->notification = $serviceUser->notifications()->create([
            'channel' => Notification::SMS,
            'recipient' => $serviceUser->phone,
            'message' => $this->message,
        ]);
    }
}

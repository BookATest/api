<?php

namespace App\Contracts;

use App\Notifications\Sms\Sms;

interface SmsSender
{
    /**
     * @param \App\Notifications\Sms\Sms $sms
     */
    public function send(Sms $sms);
}

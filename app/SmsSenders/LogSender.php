<?php

namespace App\SmsSenders;

use App\Contracts\SmsSender;
use App\Notifications\Sms\Sms;

class LogSender implements SmsSender
{
    /**
     * @param \App\Notifications\Sms\Sms $sms
     */
    public function send(Sms $sms)
    {
        $text = 'Notification sent' . PHP_EOL;
        $text .= "To: [{$sms->getTo()}]" . PHP_EOL;
        $text .= "Message: [{$sms->getMessage()}]" . PHP_EOL;
        $text .= "Notification: [{$sms->getNotification()->id}]";

        logger()->info($text);
    }
}

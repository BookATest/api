<?php

declare(strict_types=1);

namespace App\SmsSenders;

use App\Contracts\SmsSender;
use App\Notifications\Sms\Sms;
use Twilio\Rest\Client;

class TwilioSender implements SmsSender
{
    /**
     * @var \Twilio\Rest\Client
     */
    protected $client;

    /**
     * TwilioSender constructor.
     */
    public function __construct()
    {
        $this->client = new Client(
            config('sms.drivers.twilio.sid'),
            config('sms.drivers.twilio.token'),
            config('sms.drivers.twilio.account_sid')
        );
    }

    /**
     * @param \App\Notifications\Sms\Sms $sms
     */
    public function send(Sms $sms)
    {
        $to = mb_substr($sms->getTo(), 1);
        $to = '+44' . $to;

        $this->client->messages->create($to, [
            'from' => config('sms.drivers.twilio.from'),
            'body' => $sms->getMessage(),
        ]);
    }
}

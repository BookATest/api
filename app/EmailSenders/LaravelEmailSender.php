<?php

namespace App\EmailSenders;

use App\Contracts\EmailSender;
use App\Notifications\Email\Email;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Message;

class LaravelEmailSender implements EmailSender
{
    /**
     * @var \Illuminate\Contracts\Mail\Mailer
     */
    protected $mailer;

    /**
     * LaravelEmailSender constructor.
     *
     * @param \Illuminate\Contracts\Mail\Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param \App\Notifications\Email\Email $email
     */
    public function send(Email $email)
    {
        $this->mailer->raw($email->getMessage(), function (Message $message) use ($email) {
            $message->to($email->getTo())
                ->subject($email->getSubject());
        });
    }
}

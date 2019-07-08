<?php

namespace App\Contracts;

use App\Notifications\Email\Email;

interface EmailSender
{
    /**
     * @param \App\Notifications\Email\Email $email
     */
    public function send(Email $email);
}

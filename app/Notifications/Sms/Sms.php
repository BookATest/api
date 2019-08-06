<?php

namespace App\Notifications\Sms;

use App\Contracts\SmsSender;
use App\Models\Notification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class Sms implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var string
     */
    protected $to;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var \App\Models\Notification
     */
    protected $notification;

    /**
     * Sms constructor.
     */
    public function __construct()
    {
        $this->queue = config('sms.queue');
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return \App\Models\Notification
     */
    public function getNotification(): Notification
    {
        return $this->notification;
    }

    /**
     * Execute the job.
     *
     * @param \App\Contracts\SmsSender $smsSender
     */
    public function handle(SmsSender $smsSender)
    {
        try {
            // Send the SMS.
            $smsSender->send($this);

            // Update the notification.
            if ($this->notification) {
                $this->notification->channel = Notification::SMS;
                $this->notification->recipient = $this->to;
                $this->notification->message = $this->message;
                $this->notification->save();

                // TODO: $this->notification->update(['sent_at' => Date::now()]);
            }
        } catch (Exception $exception) {
            // Log the error.
            logger()->error($exception);

            // Update the notification.
            if ($this->notification) {
                // TODO: $this->notification->update(['failed_at' => Date::now()]);
            }
        }
    }
}

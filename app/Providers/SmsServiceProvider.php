<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot()
    {
        switch (config('sms.driver')) {
            case 'log':
                $this->app->singleton(\App\Contracts\SmsSender::class, \App\SmsSenders\LogSender::class);
                break;
            case 'twilio':
                $this->app->singleton(\App\Contracts\SmsSender::class, \App\SmsSenders\TwilioSender::class);
                break;
        }
    }

    /**
     * Register services.
     */
    public function register()
    {
        //
    }
}

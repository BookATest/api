<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
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
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

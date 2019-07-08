<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class EmailServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $this->app->singleton(\App\Contracts\EmailSender::class, \App\EmailSenders\LaravelEmailSender::class);
    }

    /**
     * Register services.
     */
    public function register()
    {
        //
    }
}

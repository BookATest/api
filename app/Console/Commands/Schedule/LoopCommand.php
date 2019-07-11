<?php

namespace App\Console\Commands\Schedule;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class LoopCommand extends Command
{
    const ONE_MINUTE = 60;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:loop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the scheduler every second';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        while (true) {
            $time = Date::now()->toDateTimeString();
            $this->line("Running scheduler [$time]");

            $this->call('schedule:run');

            sleep(static::ONE_MINUTE);
        }
    }
}

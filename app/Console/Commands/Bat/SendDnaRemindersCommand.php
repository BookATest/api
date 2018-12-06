<?php

namespace App\Console\Commands\Bat;

use App\Models\Appointment;
use App\Notifications\Email\CommunityWorker\DnaReminderEmail;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\DispatchesJobs;

class SendDnaRemindersCommand extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bat:send-dna-reminders
                            {--minutes-after=30 : The number of minutes after the appointment has finished to send the reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send DNA reminders to community workers';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $minutesAfter = (int)$this->option('minutes-after');

        Appointment::query()
            ->with('user')
            ->finishedXMinutesAgo($minutesAfter)
            ->chunk(200, function (Collection $appointments) {
                $appointments->each(function (Appointment $appointment) {
                    // Dispatch the job to send the email.
                    $this->dispatch(new DnaReminderEmail($appointment));

                    // Output the success message.
                    $this->info("Sent DNA reminder for appointment [$appointment->id]");
                });
            });
    }
}

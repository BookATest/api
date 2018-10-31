<?php

namespace App\Console\Commands\Bat;

use App\Models\Appointment;
use App\Notifications\Sms\AppointmentReminderSms;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\DispatchesJobs;

class SendAppointmentRemindersCommand extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bat:send-appointment-reminders
                            {--minutes-before=120 : The number of minutes before the appointment to send the reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send appointment reminders to service users';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $minutesBefore = (int)$this->option('minutes-before');
        $startAt = now()->addMinutes($minutesBefore);

        Appointment::query()
            ->with('serviceUser')
            ->where('start_at', '=', $startAt)
            ->booked()
            ->chunk(200, function (Collection $appointments) {
                $appointments->each(function (Appointment $appointment) {
                    // Dispatch the job to send the SMS.
                    $this->dispatch(new AppointmentReminderSms($appointment));

                    // Output the success message.
                    $this->info("Sent appointment reminder for appointment [$appointment->id]");
                });
            });
    }
}

<?php

namespace App\Console\Commands\Bat;

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\Email\ClinicAdmin\DnaFollowUpEmail;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\DispatchesJobs;

class SendDnaFollowUpsCommand extends Command
{
    use DispatchesJobs;

    const MINUTES_IN_DAY = 1440;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bat:send-dna-follow-ups
                            {--minutes-after=' . self::MINUTES_IN_DAY . ' : The number of minutes after the appointment has finished to send the follow up}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send DNA follow ups to clinic admins';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $minutesAfter = (int)$this->option('minutes-after');

        Appointment::query()
            ->with('clinic.clinicAdmins')
            ->whereHas('clinic', function (Builder $query) {
                $query->where('clinics.send_dna_follow_ups', '=', true);
            })
            ->booked()
            ->dnaUnactioned()
            ->finishedXMinutesAgo($minutesAfter)
            ->chunk(200, function (Collection $appointments) {
                // Loop through each chunk of appointments.
                $appointments->each(function (Appointment $appointment) {
                    // Loop through each clinic admin.
                    $appointment->clinic->clinicAdmins->each(function (User $user) use ($appointment) {
                        // Dispatch the job to send the email.
                        $this->dispatch(new DnaFollowUpEmail($appointment, $user));

                        // Output the success message.
                        $this->info("Sent DNA follow up for appointment [$appointment->id]");
                    });
                });
            });
    }
}

<?php

declare(strict_types=1);

namespace App\Console\Commands\Bat;

use App\Models\AppointmentSchedule;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CreateRepeatingAppointmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bat:create-repeating-appointments 
                            {--days-to-skip=60 : The number of days to skip}
                            {--days-up-to=90 : The number of days to create appointments up to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates appointments from appointment schedules';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $appointmentSchedules = AppointmentSchedule::query()->count();
        $successful = 0;
        $failed = 0;

        // Output the number of appointment schedules.
        $this->line("Creating appointments for $appointmentSchedules appointment schedules...");

        AppointmentSchedule::query()->chunk(200, function (Collection $appointmentSchedules) use (&$successful, &$failed) {
            $appointmentSchedules->each(function (AppointmentSchedule $appointmentSchedule) use (&$successful, &$failed) {
                // Output creating message.
                $this->line("Creating appointments for appointment schedule [$appointmentSchedule->id]...");

                try {
                    // Attempt to create.
                    $appointmentSchedule->createAppointments(
                        $this->option('days-to-skip'),
                        $this->option('days-up-to')
                    );

                    // Output success message.
                    $this->info("Created appointments for appointment schedule [$appointmentSchedule->id]");

                    // Increment successful.
                    $successful++;
                } catch (\Throwable $throwable) {
                    // Output error message.
                    $this->error("Failed to create appointments for appointment schedule [$appointmentSchedule->id]");

                    // Increment failed.
                    $failed++;
                }
            });
        });

        if ($failed > 0) {
            $this->error("Created appointments for $successful appointment schedules. Failed creating appointments for $failed appointment schedules.");
        } else {
            $this->info("Created appointments for $successful appointment schedules.");
        }
    }
}

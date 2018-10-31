<?php

namespace Tests\Unit\Commands\Bat;

use App\Console\Commands\Bat\SendAppointmentRemindersCommand;
use App\Models\Appointment;
use App\Models\ServiceUser;
use App\Notifications\Sms\ServiceUser\AppointmentReminderSms;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendAppointmentRemindersCommandTest extends TestCase
{
    public function test_appointment_reminder_sent_out()
    {
        Queue::fake();

        Carbon::setTestNow(now()->startOfWeek());

        $serviceUser = factory(ServiceUser::class)->create();
        $appointment = factory(Appointment::class)->create(['start_at' => now()->hour(12)]);
        $appointment->book($serviceUser);

        Carbon::setTestNow(now()->startOfWeek()->hour(10));

        $this->artisan(SendAppointmentRemindersCommand::class);

        Queue::assertPushed(AppointmentReminderSms::class);
    }

    public function test_no_reminder_sent_out_for_cancelled_appointment()
    {
        Queue::fake();

        Carbon::setTestNow(now()->startOfWeek());

        $serviceUser = factory(ServiceUser::class)->create();
        $appointment = factory(Appointment::class)->create(['start_at' => now()->hour(12)]);
        $appointment->book($serviceUser);

        Carbon::setTestNow(now()->startOfWeek()->hour(10)->minute(1));

        $this->artisan(SendAppointmentRemindersCommand::class);

        Queue::assertNotPushed(AppointmentReminderSms::class);
    }
}

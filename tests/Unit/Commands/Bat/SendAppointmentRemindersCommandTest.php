<?php

namespace Tests\Unit\Commands\Bat;

use App\Console\Commands\Bat\SendAppointmentRemindersCommand;
use App\Models\Appointment;
use App\Models\ServiceUser;
use App\Notifications\Sms\ServiceUser\AppointmentReminderSms;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendAppointmentRemindersCommandTest extends TestCase
{
    public function test_appointment_reminder_sent_out()
    {
        Queue::fake();

        Date::setTestNow(Date::now()->startOfWeek());

        $serviceUser = factory(ServiceUser::class)->create();
        $appointment = factory(Appointment::class)->create(['start_at' => Date::now()->hour(12)]);
        $appointment->book($serviceUser);

        Date::setTestNow(Date::now()->startOfWeek()->hour(12)->subMinutes(SendAppointmentRemindersCommand::MINUTES_IN_DAY));

        $this->artisan(SendAppointmentRemindersCommand::class);

        Queue::assertPushed(AppointmentReminderSms::class);
    }

    public function test_no_reminder_sent_out_for_cancelled_appointment()
    {
        Queue::fake();

        Date::setTestNow(Date::now()->startOfWeek());

        $serviceUser = factory(ServiceUser::class)->create();
        $appointment = factory(Appointment::class)->create(['start_at' => Date::now()->hour(12)]);
        $appointment->book($serviceUser);

        Date::setTestNow(Date::now()->startOfWeek()->hour(12)->subMinutes(SendAppointmentRemindersCommand::MINUTES_IN_DAY)->subMinute());

        $this->artisan(SendAppointmentRemindersCommand::class);

        Queue::assertNotPushed(AppointmentReminderSms::class);
    }
}

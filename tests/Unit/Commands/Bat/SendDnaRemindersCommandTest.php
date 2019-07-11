<?php

namespace Tests\Unit\Commands\Bat;

use App\Console\Commands\Bat\SendDnaRemindersCommand;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ServiceUser;
use App\Notifications\Email\CommunityWorker\DnaReminderEmail;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendDnaRemindersCommandTest extends TestCase
{
    public function test_dna_reminders_sent_out()
    {
        Queue::fake();

        CarbonImmutable::setTestNow(Date::now()->startOfWeek());

        $clinic = factory(Clinic::class)->create([
            'appointment_duration' => 60, // 1 hour
        ]);
        $appointment = factory(Appointment::class)->create([
            'clinic_id' => $clinic->id,
            'start_at' => Date::now()->subHour()->subMinutes(30),
        ]);
        $serviceUser = factory(ServiceUser::class)->create();
        $appointment->book($serviceUser, Date::now()->subHours(2));

        $this->artisan(SendDnaRemindersCommand::class);

        Queue::assertPushed(DnaReminderEmail::class);
    }

    public function test_no_dna_reminders_sent_out_when_none_due()
    {
        Queue::fake();

        CarbonImmutable::setTestNow(Date::now()->startOfWeek());

        $clinic = factory(Clinic::class)->create([
            'appointment_duration' => 60, // 1 hour
        ]);
        $appointment = factory(Appointment::class)->create([
            'clinic_id' => $clinic->id,
            'start_at' => Date::now()->subHour()->subMinutes(35),
        ]);
        $serviceUser = factory(ServiceUser::class)->create();
        $appointment->book($serviceUser, Date::now()->subHours(2));

        $this->artisan(SendDnaRemindersCommand::class);

        Queue::assertNotPushed(DnaReminderEmail::class);
    }
}

<?php

namespace Tests\Unit\Commands\Bat;

use App\Console\Commands\Bat\SendDnaFollowUpsCommand;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ServiceUser;
use App\Models\User;
use App\Notifications\Email\ClinicAdmin\DnaFollowUpEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendDnaFollowUpsCommandTest extends TestCase
{
    public function test_dna_follow_ups_sent_out()
    {
        Queue::fake();

        Carbon::setTestNow(now()->startOfWeek());

        $clinic = factory(Clinic::class)->create([
            'appointment_duration' => 60, // 1 hour
        ]);
        factory(User::class)->create()->makeClinicAdmin($clinic);
        $appointment = factory(Appointment::class)->create([
            'clinic_id' => $clinic->id,
            'start_at' => now()->subHour()->subMinutes(SendDnaFollowUpsCommand::MINUTES_IN_DAY),
        ]);
        $serviceUser = factory(ServiceUser::class)->create();
        $appointment->book($serviceUser);

        $this->artisan(SendDnaFollowUpsCommand::class);

        Queue::assertPushed(DnaFollowUpEmail::class);
    }

    public function test_no_dna_follow_ups_sent_out_when_none_due()
    {
        Queue::fake();

        Carbon::setTestNow(now()->startOfWeek());

        $clinic = factory(Clinic::class)->create([
            'appointment_duration' => 60, // 1 hour
        ]);
        factory(User::class)->create()->makeClinicAdmin($clinic);
        $appointment = factory(Appointment::class)->create([
            'clinic_id' => $clinic->id,
            'start_at' => now()->subHour()->subMinutes(35),
        ]);
        $serviceUser = factory(ServiceUser::class)->create();
        $appointment->book($serviceUser);

        $this->artisan(SendDnaFollowUpsCommand::class);

        Queue::assertNotPushed(DnaFollowUpEmail::class);
    }

    public function test_no_dna_follow_ups_sent_out_when_dna_status_actioned()
    {
        Queue::fake();

        Carbon::setTestNow(now()->startOfWeek());

        $clinic = factory(Clinic::class)->create([
            'appointment_duration' => 60, // 1 hour
        ]);
        factory(User::class)->create()->makeClinicAdmin($clinic);
        $appointment = factory(Appointment::class)->create([
            'clinic_id' => $clinic->id,
            'start_at' => now()->subHour()->subMinutes(SendDnaFollowUpsCommand::MINUTES_IN_DAY),
        ]);
        $serviceUser = factory(ServiceUser::class)->create();
        $appointment->book($serviceUser)->setDnaStatus(Appointment::ATTENDED);

        $this->artisan(SendDnaFollowUpsCommand::class);

        Queue::assertNotPushed(DnaFollowUpEmail::class);
    }
}

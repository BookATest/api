<?php

namespace Tests\Unit\Commands\Bat;

use App\Console\Commands\Bat\CreateRepeatingAppointmentsCommand;
use App\Models\AppointmentSchedule;
use App\Models\Clinic;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class CreateRepeatingAppointmentsCommandTest extends TestCase
{
    public function test_appointments_created()
    {
        $startDate = now()->startOfWeek();
        Carbon::setTestNow($startDate);

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeOrganisationAdmin();

        /** @var \App\Models\AppointmentSchedule $appointmentSchedule */
        $appointmentSchedule = AppointmentSchedule::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'weekly_on' => 1,
            'weekly_at' => '12:00:00',
        ]);

        $appointmentSchedule->createAppointments(0);
        $this->assertEquals(13, $appointmentSchedule->appointments()->count());
        $this->assertEquals(
            0,
            $appointmentSchedule->appointments()
                ->where('start_at', '>', $startDate->copy()->addDays(90))
                ->count()
        );

        $this->assertDatabaseMissing('appointments', [
            'appointment_schedule_id' => $appointmentSchedule->id,
            'start_at' => $startDate->copy()->addWeeks(13)->hour(12)->toDateTimeString(),
        ]);
        $this->assertDatabaseMissing('appointments', [
            'appointment_schedule_id' => $appointmentSchedule->id,
            'start_at' => $startDate->copy()->addWeeks(14)->hour(12)->toDateTimeString(),
        ]);
        $this->assertDatabaseMissing('appointments', [
            'appointment_schedule_id' => $appointmentSchedule->id,
            'start_at' => $startDate->copy()->addWeeks(15)->hour(12)->toDateTimeString(),
        ]);
        $this->assertDatabaseMissing('appointments', [
            'appointment_schedule_id' => $appointmentSchedule->id,
            'start_at' => $startDate->copy()->addWeeks(16)->hour(12)->toDateTimeString(),
        ]);

        Carbon::setTestNow($startDate->copy()->addMonth());

        $this->artisan(CreateRepeatingAppointmentsCommand::class);

        $this->assertEquals(18, $appointmentSchedule->appointments()->count());
        $this->assertEquals(
            0,
            $appointmentSchedule->appointments()
                ->where('start_at', '>', $startDate->copy()->addMonth()->addDays(90))
                ->count()
        );

        $this->assertDatabaseHas('appointments', [
            'appointment_schedule_id' => $appointmentSchedule->id,
            'start_at' => $startDate->copy()->addWeeks(13)->hour(12)->toDateTimeString(),
        ]);
        $this->assertDatabaseHas('appointments', [
            'appointment_schedule_id' => $appointmentSchedule->id,
            'start_at' => $startDate->copy()->addWeeks(14)->hour(12)->toDateTimeString(),
        ]);
        $this->assertDatabaseHas('appointments', [
            'appointment_schedule_id' => $appointmentSchedule->id,
            'start_at' => $startDate->copy()->addWeeks(15)->hour(12)->toDateTimeString(),
        ]);
        $this->assertDatabaseHas('appointments', [
            'appointment_schedule_id' => $appointmentSchedule->id,
            'start_at' => $startDate->copy()->addWeeks(16)->hour(12)->toDateTimeString(),
        ]);
    }

    public function test_appointments_not_duplicated()
    {
        $startDate = now()->startOfWeek();
        Carbon::setTestNow($startDate);

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeOrganisationAdmin();

        /** @var \App\Models\AppointmentSchedule $appointmentSchedule */
        $appointmentSchedule = AppointmentSchedule::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'weekly_on' => 1,
            'weekly_at' => '12:00:00',
        ]);

        $appointmentSchedule->appointments()->create([
            'user_id' => $appointmentSchedule->user_id,
            'clinic_id' => $appointmentSchedule->clinic_id,
            'appointment_schedule_id' => $appointmentSchedule->id,
            'start_at' => $startDate->hour(12),
        ]);

        $appointmentSchedule->createAppointments(0);

        $this->assertEquals(
            1,
            $appointmentSchedule->appointments()
                ->where('appointment_schedule_id', '=', $appointmentSchedule->id)
                ->where('start_at', '=', $startDate->hour(12)->toDateTimeString())
                ->count()
        );
    }
}

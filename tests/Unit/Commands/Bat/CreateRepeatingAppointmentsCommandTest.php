<?php

namespace Tests\Unit\Commands\Bat;

use App\Console\Commands\Bat\CreateRepeatingAppointmentsCommand;
use App\Models\AppointmentSchedule;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class CreateRepeatingAppointmentsCommandTest extends TestCase
{
    public function test_appointments_created()
    {
        $startDate = Date::now()->startOfWeek();
        Date::setTestNow($startDate);

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
                ->where('start_at', '>', $startDate->addDays(90)->timezone('UTC'))
                ->count()
        );

        $this->assertDatabaseMissing('appointments', [
            'appointment_schedule_id' => $appointmentSchedule->id,
            'start_at' => $startDate->addWeeks(13)->hour(12)->timezone('UTC')->toDateTimeString(),
        ]);
        $this->assertDatabaseMissing('appointments', [
            'appointment_schedule_id' => $appointmentSchedule->id,
            'start_at' => $startDate->addWeeks(14)->hour(12)->timezone('UTC')->toDateTimeString(),
        ]);
        $this->assertDatabaseMissing('appointments', [
            'appointment_schedule_id' => $appointmentSchedule->id,
            'start_at' => $startDate->addWeeks(15)->hour(12)->timezone('UTC')->toDateTimeString(),
        ]);
        $this->assertDatabaseMissing('appointments', [
            'appointment_schedule_id' => $appointmentSchedule->id,
            'start_at' => $startDate->addWeeks(16)->hour(12)->timezone('UTC')->toDateTimeString(),
        ]);

        Date::setTestNow($startDate->addWeeks(4));

        $this->artisan(CreateRepeatingAppointmentsCommand::class);

        $this->assertEquals(17, $appointmentSchedule->appointments()->count());
        $this->assertEquals(
            0,
            $appointmentSchedule->appointments()
                ->where('start_at', '>', $startDate->addMonth()->addDays(90)->timezone('UTC'))
                ->count()
        );

        $this->assertDatabaseHas('appointments', [
            'appointment_schedule_id' => $appointmentSchedule->id,
            'start_at' => $startDate->addWeeks(13)->hour(12)->timezone('UTC')->toDateTimeString(),
        ]);
        $this->assertDatabaseHas('appointments', [
            'appointment_schedule_id' => $appointmentSchedule->id,
            'start_at' => $startDate->addWeeks(14)->hour(12)->timezone('UTC')->toDateTimeString(),
        ]);
        $this->assertDatabaseHas('appointments', [
            'appointment_schedule_id' => $appointmentSchedule->id,
            'start_at' => $startDate->addWeeks(15)->hour(12)->timezone('UTC')->toDateTimeString(),
        ]);
        $this->assertDatabaseHas('appointments', [
            'appointment_schedule_id' => $appointmentSchedule->id,
            'start_at' => $startDate->addWeeks(16)->hour(12)->timezone('UTC')->toDateTimeString(),
        ]);
    }

    public function test_appointments_not_duplicated()
    {
        $startDate = Date::now()->startOfWeek();
        Date::setTestNow($startDate);

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
                ->where('start_at', '=', $startDate->hour(12)->timezone('UTC')->toDateTimeString())
                ->count()
        );
    }

    public function test_appointment_time_remains_the_same_during_bst()
    {
        // 2019-03-31 is Sunday when British Summer Time begins.
        Date::setTestNow(
            Date::create(2019, 3, 24)
        );

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeOrganisationAdmin();

        /** @var \App\Models\AppointmentSchedule $appointmentSchedule */
        $appointmentSchedule = AppointmentSchedule::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'weekly_on' => 7,
            'weekly_at' => '01:30:00',
        ]);

        $appointmentSchedule->createAppointments(0, 14);
        $appointments = $appointmentSchedule->appointments()->orderBy('start_at')->get();

        $this->assertCount(2, $appointments);
        $this->assertEquals(
            "2019-03-24T01:30:00+00:00",
            $appointments[0]->start_at->toIso8601String()
        );
        /*
         * Since the appointment on 2019-03-31 falls within a non-existant time,
         * neither "2019-03-31T02:30:00+01:00" nor "2019-03-31T01:30:00+00:00"
         * should be created, and instead the appointment creation should be
         * skipped.
         */
        $this->assertEquals(
            "2019-04-07T01:30:00+01:00",
            $appointments[1]->start_at->toIso8601String()
        );
    }

    public function test_appointment_time_remains_the_same_after_bst()
    {
        // 2019-10-27 is Sunday when British Summer Time ends.
        Date::setTestNow(
            Date::create(2019, 10, 20)
        );

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeOrganisationAdmin();

        /** @var \App\Models\AppointmentSchedule $appointmentSchedule */
        $appointmentSchedule = AppointmentSchedule::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'weekly_on' => 7,
            'weekly_at' => '01:30:00',
        ]);

        $appointmentSchedule->createAppointments(0, 14);
        $appointments = $appointmentSchedule->appointments()->orderBy('start_at')->get();

        $this->assertCount(3, $appointments);
        $this->assertEquals(
            "2019-10-20T01:30:00+01:00",
            $appointments[0]->start_at->toIso8601String()
        );
        $this->assertEquals(
            "2019-10-27T01:30:00+00:00",
            $appointments[1]->start_at->toIso8601String()
        );
        $this->assertEquals(
            "2019-11-03T01:30:00+00:00",
            $appointments[2]->start_at->toIso8601String()
        );
    }
}

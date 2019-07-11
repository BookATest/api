<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ServiceUser;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Laravel\Passport\Passport;
use Tests\TestCase;

class StatsTest extends TestCase
{
    /*
     * List them.
     */

    public function test_guest_cannot_list_them()
    {
        $response = $this->json('GET', '/v1/stats');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_can_list_them()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('GET', '/v1/stats');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                'total_appointments' => 0,
                'appointments_available' => 0,
                'appointments_booked' => 0,
                'attendance_rate' => null,
                'did_not_attend_rate' => null,
                'start_at' => Date::today()->startOfWeek()->toDateString(),
                'end_at' => Date::today()->endOfWeek()->toDateString(),
            ]
        ]);
    }

    public function test_global_stats_are_correct()
    {
        CarbonImmutable::setTestNow(Date::now()->startOfWeek());

        $clinic = factory(Clinic::class)->create(['appointment_duration' => 60]);
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        /*
         * Available appointments.
         */
        factory(Appointment::class)->create([
            'user_id' => $user->id,
            'start_at' => Date::today()->addDay(),
        ]);

        factory(Appointment::class)->create([
            'user_id' => $user->id,
            'start_at' => Date::today()->addDay()->addHour(),
        ]);

        /*
         * Appointment next week - so shouldn't show up in stats.
         */
        factory(Appointment::class)->create([
            'user_id' => $user->id,
            'start_at' => Date::today()->addDays(10),
        ]);

        /*
         * Booked appointments.
         */
        factory(Appointment::class)->create([
            'user_id' => $user->id,
            'start_at' => Date::today()->addDay()->addHours(2),
            'service_user_id' => factory(ServiceUser::class)->create()->id,
            'booked_at' => Date::now(),
            'consented_at' => Date::now(),
        ]);

        factory(Appointment::class)->create([
            'user_id' => $user->id,
            'start_at' => Date::today()->addDay()->addHours(3),
            'service_user_id' => factory(ServiceUser::class)->create()->id,
            'booked_at' => Date::now(),
            'consented_at' => Date::now(),
        ]);

        factory(Appointment::class)->create([
            'user_id' => $user->id,
            'start_at' => Date::today()->addDay()->addHours(4),
            'service_user_id' => factory(ServiceUser::class)->create()->id,
            'booked_at' => Date::now(),
            'consented_at' => Date::now(),
            'did_not_attend' => true,
        ]);

        Passport::actingAs($user);
        $response = $this->json('GET', '/v1/stats');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                'total_appointments' => 5,
                'appointments_available' => 2,
                'appointments_booked' => 3,
                'attendance_rate' => null,
                'did_not_attend_rate' => 20,
                'start_at' => Date::today()->startOfWeek()->toDateString(),
                'end_at' => Date::today()->endOfWeek()->toDateString(),
            ]
        ]);
    }

    public function test_clinic_stats_are_correct()
    {
        CarbonImmutable::setTestNow(Date::now()->startOfWeek());

        $clinic = factory(Clinic::class)->create(['appointment_duration' => 60]);
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        /*
         * Available appointments.
         */
        factory(Appointment::class)->create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'start_at' => Date::today()->addDay(),
        ]);

        factory(Appointment::class)->create([
            'user_id' => $user->id,
            'start_at' => Date::today()->addDay()->addHour(),
        ]);

        /*
         * Appointment next week - so shouldn't show up in stats.
         */
        factory(Appointment::class)->create([
            'user_id' => $user->id,
            'start_at' => Date::today()->addDays(10),
        ]);

        /*
         * Booked appointments.
         */
        factory(Appointment::class)->create([
            'user_id' => $user->id,
            'start_at' => Date::today()->addDay()->addHours(2),
            'service_user_id' => factory(ServiceUser::class)->create()->id,
            'booked_at' => Date::now(),
            'consented_at' => Date::now(),
        ]);

        factory(Appointment::class)->create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'start_at' => Date::today()->addDay()->addHours(3),
            'service_user_id' => factory(ServiceUser::class)->create()->id,
            'booked_at' => Date::now(),
            'consented_at' => Date::now(),
        ]);

        factory(Appointment::class)->create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'start_at' => Date::today()->addDay()->addHours(4),
            'service_user_id' => factory(ServiceUser::class)->create()->id,
            'booked_at' => Date::now(),
            'consented_at' => Date::now(),
            'did_not_attend' => true,
        ]);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/stats?filter[clinic_id]=$clinic->id");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                'total_appointments' => 3,
                'appointments_available' => 1,
                'appointments_booked' => 2,
                'attendance_rate' => null,
                'did_not_attend_rate' => 33.33,
                'start_at' => Date::today()->startOfWeek()->toDateString(),
                'end_at' => Date::today()->endOfWeek()->toDateString(),
            ]
        ]);
    }
}

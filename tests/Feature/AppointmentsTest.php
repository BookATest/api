<?php

namespace Tests\Feature;

use App\Events\EndpointHit;
use App\Models\Appointment;
use App\Models\Audit;
use App\Models\Clinic;
use App\Models\ServiceUser;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AppointmentsTest extends TestCase
{
    /*
     * List them.
     */

    public function test_guest_can_only_list_available_ones()
    {
        $serviceUser = factory(ServiceUser::class)->create();
        $availableAppointment = factory(Appointment::class)->create();
        $bookedAppointment = factory(Appointment::class)->create([
            'service_user_id' => $serviceUser->id,
            'booked_at' => now(),
        ]);

        $response = $this->json('GET', '/v1/appointments');

        $availableAppointment = $availableAppointment->fresh();
        $bookedAppointment = $bookedAppointment->fresh();

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $availableAppointment->id,
                'user_id' => $availableAppointment->user_id,
                'clinic_id' => $availableAppointment->clinic_id,
                'is_repeating' => $availableAppointment->appointment_schedule_id !== null,
                'service_user_id' => $availableAppointment->service_user_id,
                'start_at' => $availableAppointment->start_at->format(Carbon::ISO8601),
                'booked_at' => optional($availableAppointment->booked_at)->format(Carbon::ISO8601),
                'did_not_attend' => $availableAppointment->did_not_attend,
                'created_at' => $availableAppointment->created_at->format(Carbon::ISO8601),
                'updated_at' => $availableAppointment->updated_at->format(Carbon::ISO8601),
            ],
        ]);
        $response->assertJsonMissing(['id' => $bookedAppointment->id]);
    }

    public function test_cw_can_list_them_all()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $serviceUser = factory(ServiceUser::class)->create();
        $availableAppointment = factory(Appointment::class)->create();
        $bookedAppointment = factory(Appointment::class)->create([
            'service_user_id' => $serviceUser->id,
            'booked_at' => now(),
        ]);

        Passport::actingAs($user);
        $response = $this->json('GET', '/v1/appointments');

        $availableAppointment = $availableAppointment->fresh();
        $bookedAppointment = $bookedAppointment->fresh();

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $availableAppointment->id,
                'user_id' => $availableAppointment->user_id,
                'clinic_id' => $availableAppointment->clinic_id,
                'is_repeating' => $availableAppointment->appointment_schedule_id !== null,
                'service_user_id' => $availableAppointment->service_user_id,
                'start_at' => $availableAppointment->start_at->format(Carbon::ISO8601),
                'booked_at' => null,
                'did_not_attend' => $availableAppointment->did_not_attend,
                'created_at' => $availableAppointment->created_at->format(Carbon::ISO8601),
                'updated_at' => $availableAppointment->updated_at->format(Carbon::ISO8601),
            ],
        ]);
        $response->assertJsonFragment([
            [
                'id' => $bookedAppointment->id,
                'user_id' => $bookedAppointment->user_id,
                'clinic_id' => $bookedAppointment->clinic_id,
                'is_repeating' => $bookedAppointment->appointment_schedule_id !== null,
                'service_user_id' => $bookedAppointment->service_user_id,
                'start_at' => $bookedAppointment->start_at->format(Carbon::ISO8601),
                'booked_at' => $bookedAppointment->booked_at->format(Carbon::ISO8601),
                'did_not_attend' => $bookedAppointment->did_not_attend,
                'created_at' => $bookedAppointment->created_at->format(Carbon::ISO8601),
                'updated_at' => $bookedAppointment->updated_at->format(Carbon::ISO8601),
            ],
        ]);
    }

    public function test_audit_created_when_listed()
    {
        Event::fake();

        $this->json('GET', '/v1/appointments');

        Event::assertDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::READ, $event->getAction());
            return true;
        });
    }

    public function test_cw_can_list_them_filtering_by_user_id()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $usersAppointment = factory(Appointment::class)->create();
        $otherAppointment = factory(Appointment::class)->create();

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/appointments?filter[user_id]={$usersAppointment->user_id}");

        $usersAppointment = $usersAppointment->fresh();
        $otherAppointment = $otherAppointment->fresh();

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $usersAppointment->id,
                'user_id' => $usersAppointment->user_id,
                'clinic_id' => $usersAppointment->clinic_id,
                'is_repeating' => $usersAppointment->appointment_schedule_id !== null,
                'service_user_id' => $usersAppointment->service_user_id,
                'start_at' => $usersAppointment->start_at->format(Carbon::ISO8601),
                'booked_at' => null,
                'did_not_attend' => $usersAppointment->did_not_attend,
                'created_at' => $usersAppointment->created_at->format(Carbon::ISO8601),
                'updated_at' => $usersAppointment->updated_at->format(Carbon::ISO8601),
            ],
        ]);
        $response->assertJsonMissing(['id' => $otherAppointment->id]);
    }

    public function test_cw_can_list_them_filtering_by_clinic_id()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $clinicsAppointment = factory(Appointment::class)->create();
        $otherAppointment = factory(Appointment::class)->create();

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/appointments?filter[clinic_id]={$clinicsAppointment->clinic_id}");

        $clinicsAppointment = $clinicsAppointment->fresh();
        $otherAppointment = $otherAppointment->fresh();

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $clinicsAppointment->id,
                'user_id' => $clinicsAppointment->user_id,
                'clinic_id' => $clinicsAppointment->clinic_id,
                'is_repeating' => $clinicsAppointment->appointment_schedule_id !== null,
                'service_user_id' => $clinicsAppointment->service_user_id,
                'start_at' => $clinicsAppointment->start_at->format(Carbon::ISO8601),
                'booked_at' => null,
                'did_not_attend' => $clinicsAppointment->did_not_attend,
                'created_at' => $clinicsAppointment->created_at->format(Carbon::ISO8601),
                'updated_at' => $clinicsAppointment->updated_at->format(Carbon::ISO8601),
            ],
        ]);
        $response->assertJsonMissing(['id' => $otherAppointment->id]);
    }

    public function test_cw_can_list_them_filtering_by_service_user_id()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $serviceUsersAppointment = factory(Appointment::class)->create([
            'service_user_id' => factory(ServiceUser::class)->create()->id,
            'booked_at' => now(),
        ]);
        $otherAppointment = factory(Appointment::class)->create([
            'service_user_id' => factory(ServiceUser::class)->create()->id,
            'booked_at' => now(),
        ]);

        Passport::actingAs($user);
        $response = $this->json('GET',
            "/v1/appointments?filter[service_user_id]={$serviceUsersAppointment->service_user_id}");

        $serviceUsersAppointment = $serviceUsersAppointment->fresh();
        $otherAppointment = $otherAppointment->fresh();

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $serviceUsersAppointment->id,
                'user_id' => $serviceUsersAppointment->user_id,
                'clinic_id' => $serviceUsersAppointment->clinic_id,
                'is_repeating' => $serviceUsersAppointment->appointment_schedule_id !== null,
                'service_user_id' => $serviceUsersAppointment->service_user_id,
                'start_at' => $serviceUsersAppointment->start_at->format(Carbon::ISO8601),
                'booked_at' => $serviceUsersAppointment->booked_at->format(Carbon::ISO8601),
                'did_not_attend' => $serviceUsersAppointment->did_not_attend,
                'created_at' => $serviceUsersAppointment->created_at->format(Carbon::ISO8601),
                'updated_at' => $serviceUsersAppointment->updated_at->format(Carbon::ISO8601),
            ],
        ]);
        $response->assertJsonMissing(['id' => $otherAppointment->id]);
    }

    public function test_cw_can_list_them_filtering_by_availability()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $availableAppointment = factory(Appointment::class)->create();
        $bookedAppointment = factory(Appointment::class)->create([
            'service_user_id' => factory(ServiceUser::class)->create()->id,
            'booked_at' => now(),
        ]);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/appointments?filter[available]=true");

        $availableAppointment = $availableAppointment->fresh();
        $bookedAppointment = $bookedAppointment->fresh();

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $availableAppointment->id,
                'user_id' => $availableAppointment->user_id,
                'clinic_id' => $availableAppointment->clinic_id,
                'is_repeating' => $availableAppointment->appointment_schedule_id !== null,
                'service_user_id' => $availableAppointment->service_user_id,
                'start_at' => $availableAppointment->start_at->format(Carbon::ISO8601),
                'booked_at' => null,
                'did_not_attend' => $availableAppointment->did_not_attend,
                'created_at' => $availableAppointment->created_at->format(Carbon::ISO8601),
                'updated_at' => $availableAppointment->updated_at->format(Carbon::ISO8601),
            ],
        ]);
        $response->assertJsonMissing(['id' => $bookedAppointment->id]);
    }

    /*
     * Create one.
     */

    public function test_guest_cannot_create_one()
    {
        $response = $this->json('POST', '/v1/appointments');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}

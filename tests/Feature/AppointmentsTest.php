<?php

namespace Tests\Feature;

use App\Events\EndpointHit;
use App\Models\Appointment;
use App\Models\AppointmentSchedule;
use App\Models\Audit;
use App\Models\Clinic;
use App\Models\ServiceUser;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Queue;
use Laravel\Passport\Passport;
use Tests\Support\ICal;
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
            'consented_at' => now(),
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
                'start_at' => $availableAppointment->start_at->toIso8601String(),
                'booked_at' => optional($availableAppointment->booked_at)->toIso8601String(),
                'consented_at' => optional($availableAppointment->consented_at)->toIso8601String(),
                'did_not_attend' => $availableAppointment->did_not_attend,
                'created_at' => $availableAppointment->created_at->toIso8601String(),
                'updated_at' => $availableAppointment->updated_at->toIso8601String(),
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
            'consented_at' => now(),
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
                'start_at' => $availableAppointment->start_at->toIso8601String(),
                'booked_at' => null,
                'consented_at' => null,
                'did_not_attend' => $availableAppointment->did_not_attend,
                'created_at' => $availableAppointment->created_at->toIso8601String(),
                'updated_at' => $availableAppointment->updated_at->toIso8601String(),
            ],
        ]);
        $response->assertJsonFragment([
            [
                'id' => $bookedAppointment->id,
                'user_id' => $bookedAppointment->user_id,
                'clinic_id' => $bookedAppointment->clinic_id,
                'is_repeating' => $bookedAppointment->appointment_schedule_id !== null,
                'service_user_id' => $bookedAppointment->service_user_id,
                'start_at' => $bookedAppointment->start_at->toIso8601String(),
                'booked_at' => $bookedAppointment->booked_at->toIso8601String(),
                'consented_at' => $bookedAppointment->consented_at->toIso8601String(),
                'did_not_attend' => $bookedAppointment->did_not_attend,
                'created_at' => $bookedAppointment->created_at->toIso8601String(),
                'updated_at' => $bookedAppointment->updated_at->toIso8601String(),
            ],
        ]);
    }

    public function test_audit_created_when_listed()
    {
        $this->fakeEvents();

        $this->json('GET', '/v1/appointments');

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::READ, $event->getAction());
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
                'start_at' => $usersAppointment->start_at->toIso8601String(),
                'booked_at' => null,
                'consented_at' => null,
                'did_not_attend' => $usersAppointment->did_not_attend,
                'created_at' => $usersAppointment->created_at->toIso8601String(),
                'updated_at' => $usersAppointment->updated_at->toIso8601String(),
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
                'start_at' => $clinicsAppointment->start_at->toIso8601String(),
                'booked_at' => null,
                'consented_at' => null,
                'did_not_attend' => $clinicsAppointment->did_not_attend,
                'created_at' => $clinicsAppointment->created_at->toIso8601String(),
                'updated_at' => $clinicsAppointment->updated_at->toIso8601String(),
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
            'consented_at' => now(),
        ]);
        $otherAppointment = factory(Appointment::class)->create([
            'service_user_id' => factory(ServiceUser::class)->create()->id,
            'booked_at' => now(),
            'consented_at' => now(),
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
                'start_at' => $serviceUsersAppointment->start_at->toIso8601String(),
                'booked_at' => $serviceUsersAppointment->booked_at->toIso8601String(),
                'consented_at' => $serviceUsersAppointment->consented_at->toIso8601String(),
                'did_not_attend' => $serviceUsersAppointment->did_not_attend,
                'created_at' => $serviceUsersAppointment->created_at->toIso8601String(),
                'updated_at' => $serviceUsersAppointment->updated_at->toIso8601String(),
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
            'consented_at' => now(),
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
                'start_at' => $availableAppointment->start_at->toIso8601String(),
                'booked_at' => null,
                'consented_at' => null,
                'did_not_attend' => $availableAppointment->did_not_attend,
                'created_at' => $availableAppointment->created_at->toIso8601String(),
                'updated_at' => $availableAppointment->updated_at->toIso8601String(),
            ],
        ]);
        $response->assertJsonMissing(['id' => $bookedAppointment->id]);
    }

    public function test_cw_can_list_them_filtering_by_start_date()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $withinRangeAppointment = factory(Appointment::class)->create(['start_at' => today()]);
        $beforeRangeAppointment = factory(Appointment::class)->create(['start_at' => today()->subWeek()]);
        $afterRangeAppointment = factory(Appointment::class)->create(['start_at' => today()->addWeeks(2)]);

        Passport::actingAs($user);
        $query = http_build_query([
            'filter[starts_after]' => today()->toIso8601String(),
            'filter[starts_before]' => today()->addWeek()->toIso8601String(),
        ]);
        $response = $this->json('GET', "/v1/appointments?$query");

        $withinRangeAppointment = $withinRangeAppointment->fresh();

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment(['id' => $withinRangeAppointment->id]);
        $response->assertJsonMissing(['id' => $beforeRangeAppointment->id]);
        $response->assertJsonMissing(['id' => $afterRangeAppointment->id]);
    }

    /*
     * Steam ICS feed.
     */

    public function test_guest_cannot_stream_ics_feed()
    {
        $response = $this->json('GET', '/v1/appointments.ics');

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_cw_can_stream_ics_feed()
    {
        /** @var \App\Models\Clinic $clinic */
        $clinic = factory(Clinic::class)->create([
            'appointment_duration' => 120,
        ]);

        /** @var \App\Models\User $user */
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        /** @var \App\Models\Appointment $appointment */
        $startAt = today()->addDay()->setTime(12, 0);
        $appointment = factory(Appointment::class)->create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'start_at' => $startAt,
        ]);

        Passport::actingAs($user);
        $query = http_build_query(['calendar_feed_token' => $user->calendar_feed_token]);
        $response = $this->json('GET', "/v1/appointments.ics?$query");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'text/calendar; charset=UTF-8');

        $iCal = new ICal($response->getContent());
        $this->assertEquals(1, count($iCal->events()));

        $event = $iCal->events()[0];

        $this->assertEquals($appointment->id, $event->uid);
        $this->assertEquals(str_replace(',', '\\,', "Appointment at {$appointment->clinic->name}"), $event->summary);
        $this->assertEquals($startAt->toDateTimeString(), $event->dateStart);
        $this->assertEquals($startAt->addMinutes($clinic->appointment_duration)->toDateTimeString(), $event->dateEnd);
    }

    /*
     * Create one.
     */

    public function test_guest_cannot_create_one()
    {
        $response = $this->json('POST', '/v1/appointments');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_create_one_for_a_different_clinic()
    {
        $clinic = factory(Clinic::class)->create();
        $anotherClinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);

        $response = $this->json('POST', '/v1/appointments', [
            'clinic_id' => $anotherClinic->id,
            'start_at' => today()->toIso8601String(),
            'is_repeating' => false,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_cw_can_create_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $startAt = today();

        Passport::actingAs($user);

        $response = $this->json('POST', '/v1/appointments', [
            'clinic_id' => $clinic->id,
            'start_at' => $startAt->toIso8601String(),
            'is_repeating' => false,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonFragment([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'is_repeating' => false,
            'service_user_id' => null,
            'start_at' => $startAt->toIso8601String(),
            'booked_at' => null,
            'consented_at' => null,
            'did_not_attend' => null,
        ]);
    }

    public function test_cw_can_create_appointment_schedule()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $startAt = today();

        Passport::actingAs($user);

        $response = $this->json('POST', '/v1/appointments', [
            'clinic_id' => $clinic->id,
            'start_at' => $startAt->toIso8601String(),
            'is_repeating' => true,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonFragment([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'is_repeating' => true,
            'service_user_id' => null,
            'start_at' => $startAt->toIso8601String(),
            'booked_at' => null,
            'consented_at' => null,
            'did_not_attend' => null,
        ]);
        $this->assertDatabaseHas('appointment_schedules', [
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
        ]);
    }

    public function test_audit_created_when_created()
    {
        $this->fakeEvents();

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);

        $this->json('POST', '/v1/appointments', [
            'clinic_id' => $clinic->id,
            'start_at' => today()->toIso8601String(),
            'is_repeating' => false,
        ]);

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::CREATE, $event->getAction());
        });
    }

    /*
     * Read one.
     */

    public function test_guest_cannot_read_booked_one()
    {
        $serviceUser = factory(ServiceUser::class)->create();
        $appointment = factory(Appointment::class)->create([
            'service_user_id' => $serviceUser->id,
            'booked_at' => now(),
            'consented_at' => now(),
        ]);

        $response = $this->json('GET', "/v1/appointments/{$appointment->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_guest_can_read_available_one()
    {
        $appointment = factory(Appointment::class)->create();

        $response = $this->json('GET', "/v1/appointments/{$appointment->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $appointment->id,
                'user_id' => $appointment->user_id,
                'clinic_id' => $appointment->clinic_id,
                'is_repeating' => $appointment->appointment_schedule_id !== null,
                'service_user_id' => $appointment->service_user_id,
                'start_at' => $appointment->start_at->toIso8601String(),
                'booked_at' => null,
                'consented_at' => null,
                'did_not_attend' => $appointment->did_not_attend,
                'created_at' => $appointment->created_at->toIso8601String(),
                'updated_at' => $appointment->updated_at->toIso8601String(),
            ],
        ]);
    }

    public function test_audit_created_when_read()
    {
        $this->fakeEvents();

        $appointment = factory(Appointment::class)->create();

        $this->json('GET', "/v1/appointments/{$appointment->id}");

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::READ, $event->getAction());
        });
    }

    public function test_service_user_name_can_be_appended()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $serviceUser = factory(ServiceUser::class)->create();
        $appointment = factory(Appointment::class)->create([
            'clinic_id' => $clinic->id,
        ]);
        $appointment->book($serviceUser);

        Passport::actingAs($user);
        $query = http_build_query(['append' => 'service_user_name']);
        $response = $this->json('GET', "/v1/appointments/{$appointment->id}?$query");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $appointment->id,
                'user_id' => $appointment->user_id,
                'clinic_id' => $appointment->clinic_id,
                'is_repeating' => $appointment->appointment_schedule_id !== null,
                'service_user_id' => $appointment->service_user_id,
                'start_at' => $appointment->start_at->toIso8601String(),
                'booked_at' => $appointment->booked_at->toIso8601String(),
                'consented_at' => $appointment->consented_at->toIso8601String(),
                'did_not_attend' => $appointment->did_not_attend,
                'service_user_name' => $serviceUser->name,
                'created_at' => $appointment->created_at->toIso8601String(),
                'updated_at' => $appointment->updated_at->toIso8601String(),
            ],
        ]);
    }

    public function test_service_user_name_is_not_appended_by_default()
    {
        $appointment = factory(Appointment::class)->create();

        $response = $this->json('GET', "/v1/appointments/{$appointment->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment(['id' => $appointment->id]);
        $response->assertJsonMissing(['service_user_name' => null]);
    }

    public function test_user_details_can_be_appended()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create([
            'display_email' => true,
            'display_phone' => false,
        ])->makeCommunityWorker($clinic);

        $serviceUser = factory(ServiceUser::class)->create();
        $appointment = factory(Appointment::class)->create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
        ]);
        $appointment->book($serviceUser);

        Passport::actingAs($user);
        $query = http_build_query(['append' => 'user_first_name,user_last_name,user_email,user_phone']);
        $response = $this->json('GET', "/v1/appointments/{$appointment->id}?$query");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $appointment->id,
                'user_id' => $appointment->user_id,
                'clinic_id' => $appointment->clinic_id,
                'is_repeating' => $appointment->appointment_schedule_id !== null,
                'service_user_id' => $appointment->service_user_id,
                'start_at' => $appointment->start_at->toIso8601String(),
                'booked_at' => $appointment->booked_at->toIso8601String(),
                'consented_at' => $appointment->consented_at->toIso8601String(),
                'did_not_attend' => $appointment->did_not_attend,
                'user_first_name' => $user->first_name,
                'user_last_name' => $user->last_name,
                'user_email' => $user->email,
                'user_phone' => null,
                'created_at' => $appointment->created_at->toIso8601String(),
                'updated_at' => $appointment->updated_at->toIso8601String(),
            ],
        ]);
    }

    /*
     * Update one.
     */

    public function test_guest_cannot_update_one()
    {
        $appointment = factory(Appointment::class)->create();

        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_update_available_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $appointment = factory(Appointment::class)->create(['clinic_id' => $clinic->id]);

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}", [
            'did_not_attend' => true,
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_cw_cannot_update_one_for_another_clinic()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $appointment = factory(Appointment::class)->create();

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}", [
            'did_not_attend' => true,
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_cw_can_update_booked_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $appointment = factory(Appointment::class)->create([
            'clinic_id' => $clinic->id,
            'service_user_id' => factory(ServiceUser::class)->create()->id,
            'booked_at' => now(),
            'consented_at' => now(),
        ]);

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}", [
            'did_not_attend' => true,
        ]);

        $appointment = $appointment->fresh();

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $appointment->id,
                'user_id' => $appointment->user_id,
                'clinic_id' => $appointment->clinic_id,
                'is_repeating' => $appointment->appointment_schedule_id !== null,
                'service_user_id' => $appointment->service_user_id,
                'start_at' => $appointment->start_at->toIso8601String(),
                'booked_at' => $appointment->booked_at->toIso8601String(),
                'consented_at' => $appointment->consented_at->toIso8601String(),
                'did_not_attend' => true,
                'created_at' => $appointment->created_at->toIso8601String(),
                'updated_at' => $appointment->updated_at->toIso8601String(),
            ],
        ]);
    }

    public function test_audit_created_when_updated()
    {
        $this->fakeEvents();

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $appointment = factory(Appointment::class)->create([
            'clinic_id' => $clinic->id,
            'service_user_id' => factory(ServiceUser::class)->create()->id,
            'booked_at' => now(),
            'consented_at' => now(),
        ]);

        Passport::actingAs($user);
        $this->json('PUT', "/v1/appointments/{$appointment->id}", [
            'did_not_attend' => true,
        ]);

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::UPDATE, $event->getAction());
        });
    }

    /*
     * Delete one.
     */

    public function test_guest_cannot_delete_one()
    {
        $appointment = factory(Appointment::class)->create();

        $response = $this->json('DELETE', "/v1/appointments/{$appointment->id}");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_delete_one_for_another_clinic()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $appointment = factory(Appointment::class)->create();

        Passport::actingAs($user);
        $response = $this->json('DELETE', "/v1/appointments/{$appointment->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_cw_can_delete_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $appointment = factory(Appointment::class)->create(['clinic_id' => $clinic]);

        Passport::actingAs($user);
        $response = $this->json('DELETE', "/v1/appointments/{$appointment->id}");

        $response->assertStatus(Response::HTTP_OK);
        $this->assertModelDeleted($appointment);
    }

    public function test_audit_created_when_deleted()
    {
        $this->fakeEvents();

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $appointment = factory(Appointment::class)->create(['clinic_id' => $clinic]);

        Passport::actingAs($user);
        $this->json('DELETE', "/v1/appointments/{$appointment->id}");

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::DELETE, $event->getAction());
        });
    }

    /*
     * Cancel one.
     */

    public function test_guest_cannot_cancel_one_without_service_user_token()
    {
        $appointment = factory(Appointment::class)->create();

        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}/cancel");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_guest_cannot_cancel_available_one()
    {
        $serviceUser = factory(ServiceUser::class)->create();
        $appointment = factory(Appointment::class)->create();

        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}/cancel", [
            'service_user_token' => $serviceUser->generateToken(),
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_guest_cannot_cancel_booked_one_for_another_su()
    {
        $serviceUser = factory(ServiceUser::class)->create();
        $appointment = factory(Appointment::class)->create([
            'service_user_id' => factory(ServiceUser::class)->create()->id,
            'booked_at' => now(),
            'consented_at' => now(),
        ]);

        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}/cancel", [
            'service_user_token' => $serviceUser->generateToken(),
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_guest_can_cancel_booked_one()
    {
        $serviceUser = factory(ServiceUser::class)->create();
        $appointment = factory(Appointment::class)->create([
            'service_user_id' => $serviceUser->id,
            'booked_at' => now(),
            'consented_at' => now(),
        ]);

        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}/cancel", [
            'service_user_token' => $serviceUser->generateToken(),
        ]);

        $appointment = $appointment->fresh();

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $appointment->id,
                'user_id' => $appointment->user_id,
                'clinic_id' => $appointment->clinic_id,
                'is_repeating' => $appointment->appointment_schedule_id !== null,
                'service_user_id' => null,
                'start_at' => $appointment->start_at->toIso8601String(),
                'booked_at' => null,
                'consented_at' => null,
                'did_not_attend' => $appointment->did_not_attend,
                'created_at' => $appointment->created_at->toIso8601String(),
                'updated_at' => $appointment->updated_at->toIso8601String(),
            ],
        ]);
    }

    public function test_cw_cannot_cancel_available_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $appointment = factory(Appointment::class)->create([
            'clinic_id' => $clinic->id,
        ]);

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}/cancel");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_cw_cannot_cancel_one_for_another_clinic()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $appointment = factory(Appointment::class)->create([
            'clinic_id' => factory(Clinic::class)->create()->id,
            'service_user_id' => factory(ServiceUser::class)->create()->id,
            'booked_at' => now(),
            'consented_at' => now(),
        ]);

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}/cancel");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_cw_can_cancel_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $appointment = factory(Appointment::class)->create([
            'clinic_id' => $clinic->id,
            'service_user_id' => factory(ServiceUser::class)->create()->id,
            'booked_at' => now(),
            'consented_at' => now(),
        ]);

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}/cancel");

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas($appointment->getTable(), [
            'id' => $appointment->id,
            'service_user_id' => null,
            'booked_at' => null,
            'consented_at' => null,
        ]);
    }

    public function test_audit_created_when_canceled()
    {
        $this->fakeEvents();

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $appointment = factory(Appointment::class)->create([
            'clinic_id' => $clinic->id,
            'service_user_id' => factory(ServiceUser::class)->create()->id,
            'booked_at' => now(),
            'consented_at' => now(),
        ]);

        Passport::actingAs($user);
        $this->json('PUT', "/v1/appointments/{$appointment->id}/cancel");

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::UPDATE, $event->getAction());
        });
    }

    public function test_notifications_sent_when_cancelled_by_service_user()
    {
        Queue::fake();

        $serviceUser = factory(ServiceUser::class)->create([
            'phone' => '00000000000',
            'email' => $this->faker->safeEmail,
        ]);
        $appointment = factory(Appointment::class)->create([
            'service_user_id' => $serviceUser->id,
            'booked_at' => now(),
            'consented_at' => now(),
        ]);

        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}/cancel", [
            'service_user_token' => $serviceUser->generateToken(),
        ]);

        $response->assertStatus(Response::HTTP_OK);
        Queue::assertPushed(\App\Notifications\Email\CommunityWorker\BookingCancelledByServiceUserEmail::class);
        Queue::assertPushed(\App\Notifications\Email\ServiceUser\BookingCancelledByServiceUserEmail::class);
        Queue::assertPushed(\App\Notifications\Sms\ServiceUser\BookingCancelledByServiceUserSms::class);
    }

    public function test_notifications_sent_when_cancelled_by_user()
    {
        Queue::fake();

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeOrganisationAdmin();
        $serviceUser = factory(ServiceUser::class)->create([
            'phone' => '00000000000',
            'email' => $this->faker->safeEmail,
        ]);
        $appointment = factory(Appointment::class)->create([
            'user_id' => $user->id,
            'clinic_id' => $clinic,
            'service_user_id' => $serviceUser->id,
            'booked_at' => now(),
            'consented_at' => now(),
        ]);

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}/cancel");

        $response->assertStatus(Response::HTTP_OK);
        Queue::assertPushed(\App\Notifications\Email\CommunityWorker\BookingCancelledByUserEmail::class);
        Queue::assertPushed(\App\Notifications\Email\ServiceUser\BookingCancelledByUserEmail::class);
        Queue::assertPushed(\App\Notifications\Sms\ServiceUser\BookingCancelledByUserSms::class);
    }

    public function test_notification_not_sent_to_community_worker_with_notification_disabled_when_cancelled_by_service_user()
    {
        Queue::fake();

        $serviceUser = factory(ServiceUser::class)->create([
            'phone' => '00000000000',
            'email' => $this->faker->safeEmail,
        ]);
        $user = factory(User::class)->create([
            'receive_cancellation_confirmations' => false,
        ])->makeOrganisationAdmin();
        $appointment = factory(Appointment::class)->create([
            'user_id' => $user->id,
            'service_user_id' => $serviceUser->id,
            'booked_at' => now(),
            'consented_at' => now(),
        ]);

        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}/cancel", [
            'service_user_token' => $serviceUser->generateToken(),
        ]);

        $response->assertStatus(Response::HTTP_OK);
        Queue::assertNotPushed(\App\Notifications\Email\CommunityWorker\BookingCancelledByServiceUserEmail::class);
    }

    public function test_notification_not_sent_to_community_worker_with_notification_disabled_when_cancelled_by_user()
    {
        Queue::fake();

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create([
            'receive_cancellation_confirmations' => false,
        ])->makeOrganisationAdmin();
        $serviceUser = factory(ServiceUser::class)->create([
            'phone' => '00000000000',
            'email' => $this->faker->safeEmail,
        ]);
        $appointment = factory(Appointment::class)->create([
            'user_id' => $user->id,
            'clinic_id' => $clinic,
            'service_user_id' => $serviceUser->id,
            'booked_at' => now(),
            'consented_at' => now(),
        ]);

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}/cancel");

        $response->assertStatus(Response::HTTP_OK);
        Queue::assertNotPushed(\App\Notifications\Email\CommunityWorker\BookingCancelledByUserEmail::class);
    }
    }

    /*
     * Delete schedule.
     */

    public function test_guest_cannot_delete_schedule()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        /** @var \App\Models\AppointmentSchedule $appointmentSchedule */
        $appointmentSchedule = AppointmentSchedule::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'weekly_on' => today()->dayOfWeek,
            'weekly_at' => today()->toTimeString(),
        ]);

        $daysToSkip = 0;
        $appointments = $appointmentSchedule->createAppointments($daysToSkip);
        $appointment = $appointments->first();

        $response = $this->json('DELETE', "/v1/appointments/{$appointment->id}/schedule");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_delete_schedule_for_another_clinic()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        /** @var \App\Models\AppointmentSchedule $appointmentSchedule */
        $appointmentSchedule = AppointmentSchedule::create([
            'user_id' => $user->id,
            'clinic_id' => factory(Clinic::class)->create()->id,
            'weekly_on' => today()->dayOfWeek,
            'weekly_at' => today()->toTimeString(),
        ]);

        $daysToSkip = 0;
        $appointments = $appointmentSchedule->createAppointments($daysToSkip);
        $appointment = $appointments->first();

        Passport::actingAs($user);
        $response = $this->json('DELETE', "/v1/appointments/{$appointment->id}/schedule");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_cw_can_delete_schedule()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        /** @var \App\Models\AppointmentSchedule $appointmentSchedule */
        $appointmentSchedule = AppointmentSchedule::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'weekly_on' => today()->dayOfWeek,
            'weekly_at' => today()->toTimeString(),
        ]);

        $daysToSkip = 0;
        $appointments = $appointmentSchedule->createAppointments($daysToSkip);
        $appointment = $appointments->first();

        Passport::actingAs($user);
        $response = $this->json('DELETE', "/v1/appointments/{$appointment->id}/schedule");

        $response->assertStatus(Response::HTTP_OK);
        $this->assertModelSoftDeleted($appointmentSchedule);
        $this->assertDatabaseMissing($appointment->getTable(), [
            'appointment_schedule_id' => $appointmentSchedule->id,
        ]);
    }

    public function test_schedule_booked_appointments_remain_when_schedule_deleted()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $serviceUser = factory(ServiceUser::class)->create();

        /** @var \App\Models\AppointmentSchedule $appointmentSchedule */
        $appointmentSchedule = AppointmentSchedule::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'weekly_on' => today()->dayOfWeek,
            'weekly_at' => today()->toTimeString(),
        ]);

        $daysToSkip = 0;
        $appointments = $appointmentSchedule->createAppointments($daysToSkip);
        $appointment = $appointments->first();
        $appointment->service_user_id = $serviceUser->id;
        $appointment->booked_at = now();
        $appointment->consented_at = $appointment->booked_at;
        $appointment->save();

        Passport::actingAs($user);
        $response = $this->json('DELETE', "/v1/appointments/{$appointment->id}/schedule");

        $response->assertStatus(Response::HTTP_OK);
        $this->assertModelSoftDeleted($appointmentSchedule);
        foreach ($appointments as $loopedAppointment) {
            if ($loopedAppointment->id === $appointment->id) {
                continue;
            }

            $this->assertDatabaseMissing($loopedAppointment->getTable(), [
                'id' => $loopedAppointment->id,
            ]);
        }
        $this->assertDatabaseHas($appointment->getTable(), ['id' => $appointment->id]);
    }

    public function test_audit_created_when_schedule_deleted()
    {
        $this->fakeEvents();

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        /** @var \App\Models\AppointmentSchedule $appointmentSchedule */
        $appointmentSchedule = AppointmentSchedule::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'weekly_on' => today()->dayOfWeek,
            'weekly_at' => today()->toTimeString(),
        ]);

        $daysToSkip = 0;
        $appointments = $appointmentSchedule->createAppointments($daysToSkip);
        $appointment = $appointments->first();

        Passport::actingAs($user);
        $this->json('DELETE', "/v1/appointments/{$appointment->id}/schedule");

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::DELETE, $event->getAction());
        });
    }
}

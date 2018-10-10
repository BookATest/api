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
use Illuminate\Support\Carbon;
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
                'start_at' => $availableAppointment->start_at->toIso8601String(),
                'booked_at' => optional($availableAppointment->booked_at)->toIso8601String(),
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
                'start_at' => $serviceUsersAppointment->start_at->toIso8601String(),
                'booked_at' => $serviceUsersAppointment->booked_at->toIso8601String(),
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
                'did_not_attend' => $availableAppointment->did_not_attend,
                'created_at' => $availableAppointment->created_at->toIso8601String(),
                'updated_at' => $availableAppointment->updated_at->toIso8601String(),
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
        ]);

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}/cancel");

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas($appointment->getTable(), [
            'id' => $appointment->id,
            'service_user_id' => null,
            'booked_at' => null,
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
        ]);

        Passport::actingAs($user);
        $this->json('PUT', "/v1/appointments/{$appointment->id}/cancel");

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::UPDATE, $event->getAction());
        });
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

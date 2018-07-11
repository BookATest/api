<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ServiceUser;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AppointmentsTest extends TestCase
{
    public function test_guest_cannot_view_all_appointments()
    {
        $response = $this->json('GET', '/v1/appointments');

        $response->assertStatus(401);
    }

    public function test_cw_can_view_all_appointments()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create();
        $user->makeCommunityWorker($clinic);
        $startAt = today()->setTime(10, 30);
        $appointment = Appointment::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'start_at' => $startAt,
        ]);

        Passport::actingAs($user);

        $response = $this->json('GET', '/v1/appointments');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'data' => [
                [
                    'id' => $appointment->id,
                    'user_id' => $user->id,
                    'clinic_id' => $clinic->id,
                    'is_repeating' => false,
                    'service_user_uuid' => null,
                    'start_at' => $startAt->toIso8601String(),
                    'booked_at' => null,
                    'did_not_attend' => null,
                ]
            ]
        ]);
    }

    public function test_guest_cannot_view_appointment()
    {
        $user = factory(User::class)->create();
        $clinic = factory(Clinic::class)->create();
        $startAt = today()->setTime(10, 30);
        $appointment = Appointment::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'start_at' => $startAt,
        ]);

        $response = $this->json('GET', "/v1/appointments/{$appointment->id}");

        $response->assertStatus(401);
    }

    public function test_cw_can_view_appointment()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create();
        $user->makeCommunityWorker($clinic);
        $startAt = today()->setTime(10, 30);
        $appointment = Appointment::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'start_at' => $startAt,
        ]);

        Passport::actingAs($user);

        $response = $this->json('GET', "/v1/appointments/{$appointment->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $appointment->id,
                'user_id' => $user->id,
                'clinic_id' => $clinic->id,
                'is_repeating' => false,
                'service_user_uuid' => null,
                'start_at' => $startAt->toIso8601String(),
                'booked_at' => null,
                'did_not_attend' => null,
            ]
        ]);
    }

    public function test_guest_cannot_update_appointment()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create();
        $startAt = today()->setTime(10, 30);
        $appointment = Appointment::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'start_at' => $startAt,
        ]);

        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}", [
            'did_not_attend' => true,
        ]);

        $response->assertStatus(401);
    }

    public function test_cw_cannot_update_someone_elses_appointment()
    {
        $clinic = factory(Clinic::class)->create();
        $ownerUser = factory(User::class)->create();
        $ownerUser->makeCommunityWorker($clinic);
        $startAt = today()->setTime(10, 30);
        $appointment = Appointment::create([
            'user_id' => $ownerUser->id,
            'clinic_id' => $clinic->id,
            'start_at' => $startAt,
        ]);
        $differentUser = factory(User::class)->create();
        $differentUser->makeCommunityWorker($clinic);

        Passport::actingAs($differentUser);

        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}", [
            'did_not_attend' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_cw_can_update_their_own_appointment()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create();
        $user->makeCommunityWorker($clinic);
        $startAt = today()->setTime(10, 30);
        $appointment = Appointment::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'start_at' => $startAt,
        ]);

        Passport::actingAs($user);

        $response = $this->json('PUT', "/v1/appointments/{$appointment->id}", [
            'did_not_attend' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $appointment->id,
                'user_id' => $user->id,
                'clinic_id' => $clinic->id,
                'is_repeating' => false,
                'service_user_uuid' => null,
                'start_at' => $startAt->toIso8601String(),
                'booked_at' => null,
                'did_not_attend' => true,
            ]
        ]);
    }

    public function test_guest_cannot_delete_appointment()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create();
        $startAt = today()->setTime(10, 30);
        $appointment = Appointment::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'start_at' => $startAt,
        ]);

        $response = $this->json('DELETE', "/v1/appointments/{$appointment->id}");

        $response->assertStatus(401);
    }

    public function test_cw_can_delete_their_own_appointment()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create();
        $user->makeCommunityWorker($clinic);
        $startAt = today()->setTime(10, 30);
        $appointment = Appointment::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'start_at' => $startAt,
        ]);
        $appointmentId = $appointment->id;

        Passport::actingAs($user);

        $response = $this->json('DELETE', "/v1/appointments/{$appointment->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'The Appointment has been successfully deleted']);
        $this->assertDatabaseMissing('appointments', ['id' => $appointmentId]);
    }

    public function test_cw_can_delete_someone_elses_appointment_at_same_clinic()
    {
        $clinic = factory(Clinic::class)->create();
        $ownerUser = factory(User::class)->create();
        $ownerUser->makeCommunityWorker($clinic);
        $startAt = today()->setTime(10, 30);
        $appointment = Appointment::create([
            'user_id' => $ownerUser->id,
            'clinic_id' => $clinic->id,
            'start_at' => $startAt,
        ]);
        $appointmentId = $appointment->id;
        $differentUser = factory(User::class)->create();
        $differentUser->makeCommunityWorker($clinic);

        Passport::actingAs($differentUser);

        $response = $this->json('DELETE', "/v1/appointments/{$appointment->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'The Appointment has been successfully deleted']);
        $this->assertDatabaseMissing('appointments', ['id' => $appointmentId]);
    }

    public function test_cw_cannot_delete_someone_elses_appointment_at_different_clinic()
    {
        $clinic = factory(Clinic::class)->create();
        $ownerUser = factory(User::class)->create();
        $ownerUser->makeCommunityWorker($clinic);
        $startAt = today()->setTime(10, 30);
        $appointment = Appointment::create([
            'user_id' => $ownerUser->id,
            'clinic_id' => $clinic->id,
            'start_at' => $startAt,
        ]);
        $differentClinic = factory(Clinic::class)->create();
        $differentUser = factory(User::class)->create();
        $differentUser->makeCommunityWorker($differentClinic);

        Passport::actingAs($differentUser);

        $response = $this->json('DELETE', "/v1/appointments/{$appointment->id}");

        $response->assertStatus(403);
    }

    public function test_cw_cannot_delete_a_booked_appointment()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create();
        $user->makeCommunityWorker($clinic);
        $serviceUser = factory(ServiceUser::class)->create();
        $startAt = today()->setTime(10, 30);
        $appointment = Appointment::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'start_at' => $startAt,
        ]);
        $appointment->service_user_uuid = $serviceUser->uuid;
        $appointment->save();

        Passport::actingAs($user);

        $response = $this->json('DELETE', "/v1/appointments/{$appointment->id}");

        $response->assertStatus(409);
    }
}

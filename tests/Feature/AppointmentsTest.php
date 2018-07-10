<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use Tests\TestCase;

class AppointmentsTest extends TestCase
{
    public function test_can_view_all_appointments_as_cw()
    {
        $user = factory(User::class)->create();
        $user->makeOrganisationAdmin();
        $clinic = factory(Clinic::class)->create();
        $startAt = today()->setTime(10, 30);
        $appointment = Appointment::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'start_at' => $startAt,
        ]);

        $response = $this->json('GET', '/v1/appointments');

        $response->assertStatus(200);
        $response->assertJsonFragment([
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

    public function test_cannot_view_all_appointments_as_guest()
    {
        $response = $this->json('GET', '/v1/appointments');

        $response->assertStatus(401);
    }
}

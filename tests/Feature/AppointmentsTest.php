<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\ServiceUser;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AppointmentsTest extends TestCase
{
    /*
     * List them.
     */

    public function test_guest_can_only_view_available_appointments()
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
            ]
        ]);
        $response->assertJsonMissing(['id' => $bookedAppointment->id]);
    }

    public function test_cw_can_view_all_appointments()
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
                'booked_at' => null,
                'did_not_attend' => $availableAppointment->did_not_attend,
                'created_at' => $availableAppointment->created_at->format(Carbon::ISO8601),
                'updated_at' => $availableAppointment->updated_at->format(Carbon::ISO8601),
            ]
        ]);
        $response->assertJsonMissing([
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
            ]
        ]);
    }
}

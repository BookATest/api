<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Response;
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
                'start_at' => today()->startOfWeek()->toDateString(),
                'end_at' => today()->endOfWeek()->toDateString(),
            ]
        ]);
    }
}

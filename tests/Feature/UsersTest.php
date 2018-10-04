<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UsersTest extends TestCase
{
    /*
     * List them.
     */

    public function test_guest_cannot_list_them()
    {
        $response = $this->json('GET', '/v1/users');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_can_list_them()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('GET', '/v1/users');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'display_email' => $user->display_email,
                    'display_phone' => $user->display_phone,
                    'include_calendar_attachment' => $user->include_calendar_attachment,
                    'roles' => [
                        [
                            'role' => Role::communityWorker()->name,
                            'clinic_id' => $clinic->id,
                        ]
                    ],
                    'created_at' => $user->created_at->format(Carbon::ISO8601),
                    'updated_at' => $user->updated_at->format(Carbon::ISO8601),
                ]
            ]
        ]);
    }
}

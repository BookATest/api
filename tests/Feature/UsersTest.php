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

    /*
     * Create one.
     */

    public function test_guest_cannot_create_one()
    {
        $response = $this->json('POST', '/v1/users');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_create_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('POST', '/v1/users');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_can_create_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        Passport::actingAs($user);
        $response = $this->json('POST', '/v1/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '07700000000',
            'password' => 'P@55word.',
            'display_email' => false,
            'display_phone' => false,
            'include_calendar_attachment' => false,
            'roles' => [
                [
                    'role' => Role::COMMUNITY_WORKER,
                    'clinic_id' => $clinic->id,
                ]
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonFragment([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '07700000000',
            'display_email' => false,
            'display_phone' => false,
            'include_calendar_attachment' => false,
            'roles' => [
                [
                    'role' => Role::COMMUNITY_WORKER,
                    'clinic_id' => $clinic->id,
                ]
            ],
        ]);
    }

    public function test_ca_cannot_create_oa()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        Passport::actingAs($user);
        $response = $this->json('POST', '/v1/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '07700000000',
            'password' => 'P@55word.',
            'display_email' => false,
            'display_phone' => false,
            'include_calendar_attachment' => false,
            'roles' => [
                [
                    'role' => Role::ORGANISATION_ADMIN,
                ]
            ],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}

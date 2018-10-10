<?php

namespace Tests\Feature;

use App\Events\EndpointHit;
use App\Models\Audit;
use App\Models\Clinic;
use App\Models\File;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UsersTest extends TestCase
{
    const BASE64_PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

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

    public function test_audit_created_when_listed()
    {
        $this->fakeEvents();

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $this->json('GET', '/v1/users');

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::READ, $event->getAction());
        });
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

    public function test_audit_created_when_created()
    {
        $this->fakeEvents();

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        Passport::actingAs($user);
        $this->json('POST', '/v1/users', [
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

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::CREATE, $event->getAction());
        });
    }

    public function test_ca_can_create_one_with_profile_picture()
    {
        Storage::fake('cloud');

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
            'profile_picture' => static::BASE64_PNG,
        ]);

        $file = File::first();

        $response->assertStatus(Response::HTTP_CREATED);
        Storage::cloud()->assertExists($file->path());
    }

    /*
     * Read one.
     */

    public function test_guest_cannot_read_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $response = $this->json('GET', "/v1/users/{$user->id}");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_can_read_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/users/{$user->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
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
        ]);
    }

    public function test_audit_created_when_read()
    {
        $this->fakeEvents();

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/users/{$user->id}");

        $response->assertStatus(Response::HTTP_OK);
        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::READ, $event->getAction());
        });
    }

    /*
     * Update one.
     */

    public function test_guest_cannot_update_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $response = $this->json('PUT', "/v1/users/{$user->id}");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_update_another_user()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $anotherUser = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/users/{$anotherUser->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_cw_can_update_them_self()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/users/{$user->id}", [
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

        $response->assertStatus(Response::HTTP_OK);
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

    public function test_ca_can_promote_ca_to_cw()
    {
        $clinic = factory(Clinic::class)->create();
        $clinicAdmin = factory(User::class)->create()->makeClinicAdmin($clinic);
        $communityWorker = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($clinicAdmin);
        $response = $this->json('PUT', "/v1/users/{$communityWorker->id}", [
            'first_name' => $communityWorker->first_name,
            'last_name' => $communityWorker->last_name,
            'email' => $communityWorker->email,
            'phone' => $communityWorker->phone,
            'display_email' => $communityWorker->display_email,
            'display_phone' => $communityWorker->display_phone,
            'include_calendar_attachment' => $communityWorker->include_calendar_attachment,
            'roles' => [
                [
                    'role' => Role::COMMUNITY_WORKER,
                    'clinic_id' => $clinic->id,
                ],
                [
                    'role' => Role::CLINIC_ADMIN,
                    'clinic_id' => $clinic->id,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'first_name' => $communityWorker->first_name,
            'last_name' => $communityWorker->last_name,
            'email' => $communityWorker->email,
            'phone' => $communityWorker->phone,
            'display_email' => $communityWorker->display_email,
            'display_phone' => $communityWorker->display_phone,
            'include_calendar_attachment' => $communityWorker->include_calendar_attachment,
            'roles' => [
                [
                    'role' => Role::COMMUNITY_WORKER,
                    'clinic_id' => $clinic->id,
                ],
                [
                    'role' => Role::CLINIC_ADMIN,
                    'clinic_id' => $clinic->id,
                ],
            ],
        ]);
    }

    public function test_ca_cannot_promote_cw_to_oa()
    {
        $clinic = factory(Clinic::class)->create();
        $clinicAdmin = factory(User::class)->create()->makeClinicAdmin($clinic);
        $communityWorker = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($clinicAdmin);
        $response = $this->json('PUT', "/v1/users/{$communityWorker->id}", [
            'first_name' => $communityWorker->first_name,
            'last_name' => $communityWorker->last_name,
            'email' => $communityWorker->email,
            'phone' => $communityWorker->phone,
            'display_email' => $communityWorker->display_email,
            'display_phone' => $communityWorker->display_phone,
            'include_calendar_attachment' => $communityWorker->include_calendar_attachment,
            'roles' => [
                [
                    'role' => Role::COMMUNITY_WORKER,
                    'clinic_id' => $clinic->id,
                ],
                [
                    'role' => Role::CLINIC_ADMIN,
                    'clinic_id' => $clinic->id,
                ],
                [
                    'role' => Role::ORGANISATION_ADMIN,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_ca_can_revoke_cw()
    {
        $clinic = factory(Clinic::class)->create();
        $clinicAdmin = factory(User::class)->create()->makeClinicAdmin($clinic);
        $communityWorker = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($clinicAdmin);
        $response = $this->json('PUT', "/v1/users/{$communityWorker->id}", [
            'first_name' => $communityWorker->first_name,
            'last_name' => $communityWorker->last_name,
            'email' => $communityWorker->email,
            'phone' => $communityWorker->phone,
            'display_email' => $communityWorker->display_email,
            'display_phone' => $communityWorker->display_phone,
            'include_calendar_attachment' => $communityWorker->include_calendar_attachment,
            'roles' => [],
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'first_name' => $communityWorker->first_name,
            'last_name' => $communityWorker->last_name,
            'email' => $communityWorker->email,
            'phone' => $communityWorker->phone,
            'display_email' => $communityWorker->display_email,
            'display_phone' => $communityWorker->display_phone,
            'include_calendar_attachment' => $communityWorker->include_calendar_attachment,
            'roles' => [],
        ]);
    }

    public function test_ca_can_revoke_cw_at_two_clinics()
    {
        $clinic = factory(Clinic::class)->create();
        $anotherClinic = factory(Clinic::class)->create();

        $clinicAdmin = factory(User::class)->create()
            ->makeClinicAdmin($clinic);
        $communityWorker = factory(User::class)->create()
            ->makeCommunityWorker($clinic)
            ->makeCommunityWorker($anotherClinic);

        Passport::actingAs($clinicAdmin);
        $response = $this->json('PUT', "/v1/users/{$communityWorker->id}", [
            'first_name' => $communityWorker->first_name,
            'last_name' => $communityWorker->last_name,
            'email' => $communityWorker->email,
            'phone' => $communityWorker->phone,
            'display_email' => $communityWorker->display_email,
            'display_phone' => $communityWorker->display_phone,
            'include_calendar_attachment' => $communityWorker->include_calendar_attachment,
            'roles' => [
                [
                    'role' => Role::COMMUNITY_WORKER,
                    'clinic_id' => $anotherClinic->id,
                ]
            ],
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'first_name' => $communityWorker->first_name,
            'last_name' => $communityWorker->last_name,
            'email' => $communityWorker->email,
            'phone' => $communityWorker->phone,
            'display_email' => $communityWorker->display_email,
            'display_phone' => $communityWorker->display_phone,
            'include_calendar_attachment' => $communityWorker->include_calendar_attachment,
            'roles' => [
                [
                    'role' => Role::COMMUNITY_WORKER,
                    'clinic_id' => $anotherClinic->id,
                ]
            ],
        ]);
    }

    public function test_ca_can_revoke_both_cw_roles_at_two_clinics()
    {
        $clinic = factory(Clinic::class)->create();
        $anotherClinic = factory(Clinic::class)->create();

        $clinicAdmin = factory(User::class)->create()
            ->makeClinicAdmin($clinic);
        $communityWorker = factory(User::class)->create()
            ->makeCommunityWorker($clinic)
            ->makeCommunityWorker($anotherClinic);

        Passport::actingAs($clinicAdmin);
        $response = $this->json('PUT', "/v1/users/{$communityWorker->id}", [
            'first_name' => $communityWorker->first_name,
            'last_name' => $communityWorker->last_name,
            'email' => $communityWorker->email,
            'phone' => $communityWorker->phone,
            'display_email' => $communityWorker->display_email,
            'display_phone' => $communityWorker->display_phone,
            'include_calendar_attachment' => $communityWorker->include_calendar_attachment,
            'roles' => [],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_ca_can_revoke_cw_at_different_clinic()
    {
        $clinic = factory(Clinic::class)->create();
        $anotherClinic = factory(Clinic::class)->create();

        $clinicAdmin = factory(User::class)->create()
            ->makeClinicAdmin($clinic);
        $communityWorker = factory(User::class)->create()
            ->makeCommunityWorker($clinic)
            ->makeCommunityWorker($anotherClinic);

        Passport::actingAs($clinicAdmin);
        $response = $this->json('PUT', "/v1/users/{$communityWorker->id}", [
            'first_name' => $communityWorker->first_name,
            'last_name' => $communityWorker->last_name,
            'email' => $communityWorker->email,
            'phone' => $communityWorker->phone,
            'display_email' => $communityWorker->display_email,
            'display_phone' => $communityWorker->display_phone,
            'include_calendar_attachment' => $communityWorker->include_calendar_attachment,
            'roles' => [
                [
                    'role' => Role::COMMUNITY_WORKER,
                    'clinic_id' => $clinic->id,
                ]
            ],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_audit_created_when_updated()
    {
        $this->fakeEvents();

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $this->json('PUT', "/v1/users/{$user->id}", [
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

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::UPDATE, $event->getAction());
        });
    }

    /*
     * Delete one.
     */

    public function test_guest_cannot_disable_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $response = $this->json('DELETE', "/v1/users/{$user->id}");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_disable_cw()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $anotherUser = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('DELETE', "/v1/users/{$anotherUser->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_cannot_disable_cw()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);
        $anotherUser = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('DELETE', "/v1/users/{$anotherUser->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_oa_can_disable_cw()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeOrganisationAdmin();
        $anotherUser = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('DELETE', "/v1/users/{$anotherUser->id}");

        $response->assertStatus(Response::HTTP_OK);
        $this->assertUserDisabled($anotherUser);
    }

    public function test_oa_can_disable_oa()
    {
        $user = factory(User::class)->create()->makeOrganisationAdmin();
        $anotherUser = factory(User::class)->create()->makeOrganisationAdmin();

        Passport::actingAs($user);
        $response = $this->json('DELETE', "/v1/users/{$anotherUser->id}");

        $response->assertStatus(Response::HTTP_OK);
        $this->assertUserDisabled($anotherUser);
    }

    public function test_audit_created_when_disabled()
    {
        $this->fakeEvents();

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeOrganisationAdmin();
        $anotherUser = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $this->json('DELETE', "/v1/users/{$anotherUser->id}");

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::DELETE, $event->getAction());
        });
    }

    /*
     * Read profile picture.
     */

    public function test_guest_can_read_profile_picture()
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
            'profile_picture' => static::BASE64_PNG,
        ]);

        $data = json_decode($response->getContent(), true);
        $createdUserId = $data['data']['id'];

        $response = $this->get("/v1/users/$createdUserId/profile-picture.png");
        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'image/png');

        $profilePictureBase64Encoded = 'data:image/png;base64,' . base64_encode($response->getContent());

        $this->assertEquals(static::BASE64_PNG, $profilePictureBase64Encoded);
    }

    /*
     * Update calendar feed token.
     */

    public function test_guest_cannot_update_cw_calendar_feed_token()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $response = $this->json('PUT', "/v1/users/$user->id/calendar-feed-token");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_update_calendar_feed_token_for_another_cw()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $anotherUser = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/users/$anotherUser->id/calendar-feed-token");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_cw_can_update_calendar_feed_token_for_them_self()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $originalToken = $user->calendar_feed_token;

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/users/$user->id/calendar-feed-token");

        $user = $user->fresh();

        $response->assertStatus(Response::HTTP_OK);
        $this->assertNotEquals($originalToken, $user->calendar_feed_token);
    }
}

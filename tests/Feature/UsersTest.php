<?php

namespace Tests\Feature;

use App\Events\EndpointHit;
use App\Models\Audit;
use App\Models\Clinic;
use App\Models\File;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UsersTest extends TestCase
{
    const BASE64_PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
    const BASE64_JPEG = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/4QkgaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJYTVAgQ29yZSA1LjUuMCI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiLz4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8P3hwYWNrZXQgZW5kPSJ3Ij8+/+0ALFBob3Rvc2hvcCAzLjAAOEJJTQQlAAAAAAAQ1B2M2Y8AsgTpgAmY7PhCfv/iAmRJQ0NfUFJPRklMRQABAQAAAlRsY21zBDAAAG1udHJSR0IgWFlaIAfiAAoAHQAOAA8AEWFjc3BBUFBMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD21gABAAAAANMtbGNtcwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAC2Rlc2MAAAEIAAAAPmNwcnQAAAFIAAAATHd0cHQAAAGUAAAAFGNoYWQAAAGoAAAALHJYWVoAAAHUAAAAFGJYWVoAAAHoAAAAFGdYWVoAAAH8AAAAFHJUUkMAAAIQAAAAIGdUUkMAAAIQAAAAIGJUUkMAAAIQAAAAIGNocm0AAAIwAAAAJG1sdWMAAAAAAAAAAQAAAAxlblVTAAAAIgAAABwAcwBSAEcAQgAgAEkARQBDADYAMQA5ADYANgAtADIALgAxAABtbHVjAAAAAAAAAAEAAAAMZW5VUwAAADAAAAAcAE4AbwAgAGMAbwBwAHkAcgBpAGcAaAB0ACwAIAB1AHMAZQAgAGYAcgBlAGUAbAB5WFlaIAAAAAAAAPbWAAEAAAAA0y1zZjMyAAAAAAABDEIAAAXe///zJQAAB5MAAP2Q///7of///aIAAAPcAADAblhZWiAAAAAAAABvoAAAOPUAAAOQWFlaIAAAAAAAACSfAAAPhAAAtsNYWVogAAAAAAAAYpcAALeHAAAY2XBhcmEAAAAAAAMAAAACZmYAAPKnAAANWQAAE9AAAApbY2hybQAAAAAAAwAAAACj1wAAVHsAAEzNAACZmgAAJmYAAA9c/9sAQwABAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEB/9sAQwEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEB/8AAEQgAAQABAwERAAIRAQMRAf/EAB8AAAEFAQEBAQEBAAAAAAAAAAABAgMEBQYHCAkKC//EALUQAAIBAwMCBAMFBQQEAAABfQECAwAEEQUSITFBBhNRYQcicRQygZGhCCNCscEVUtHwJDNicoIJChYXGBkaJSYnKCkqNDU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6g4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2drh4uPk5ebn6Onq8fLz9PX29/j5+v/EAB8BAAMBAQEBAQEBAQEAAAAAAAABAgMEBQYHCAkKC//EALURAAIBAgQEAwQHBQQEAAECdwABAgMRBAUhMQYSQVEHYXETIjKBCBRCkaGxwQkjM1LwFWJy0QoWJDThJfEXGBkaJicoKSo1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoKDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uLj5OXm5+jp6vLz9PX29/j5+v/aAAwDAQACEQMRAD8A/wA/+gD/2Q==';

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
                    'receive_booking_confirmations' => $user->receive_booking_confirmations,
                    'receive_cancellation_confirmations' => $user->receive_cancellation_confirmations,
                    'roles' => [
                        [
                            'role' => Role::communityWorker()->name,
                            'clinic_id' => $clinic->id,
                        ]
                    ],
                    'created_at' => $user->created_at->toIso8601String(),
                    'updated_at' => $user->updated_at->toIso8601String(),
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
            'receive_booking_confirmations' => false,
            'receive_cancellation_confirmations' => false,
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
            'receive_booking_confirmations' => false,
            'receive_cancellation_confirmations' => false,
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
            'receive_booking_confirmations' => false,
            'receive_cancellation_confirmations' => false,
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
            'receive_booking_confirmations' => false,
            'receive_cancellation_confirmations' => false,
            'include_calendar_attachment' => false,
            'roles' => [
                [
                    'role' => Role::COMMUNITY_WORKER,
                    'clinic_id' => $clinic->id,
                ]
            ],
            'profile_picture' => static::BASE64_JPEG,
        ]);

        $file = File::first();

        $response->assertStatus(Response::HTTP_CREATED);
        Storage::cloud()->assertExists($file->path());
    }

    public function test_ca_can_create_one_with_profile_picture_of_500KB()
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
            'receive_booking_confirmations' => false,
            'receive_cancellation_confirmations' => false,
            'include_calendar_attachment' => false,
            'roles' => [
                [
                    'role' => Role::COMMUNITY_WORKER,
                    'clinic_id' => $clinic->id,
                ]
            ],
            'profile_picture' => 'data:image/jpeg;base64,' . base64_encode(Storage::disk('testing')->get('example-500KB.jpg')),
        ]);

        $file = File::first();

        $response->assertStatus(Response::HTTP_CREATED);
        Storage::cloud()->assertExists($file->path());
    }

    public function test_ca_cannot_create_one_with_profile_picture_of_3MB()
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
            'receive_booking_confirmations' => false,
            'receive_cancellation_confirmations' => false,
            'include_calendar_attachment' => false,
            'roles' => [
                [
                    'role' => Role::COMMUNITY_WORKER,
                    'clinic_id' => $clinic->id,
                ]
            ],
            'profile_picture' => 'data:image/jpeg;base64,' . base64_encode(Storage::disk('testing')->get('example-3MB.jpg')),
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
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
                'receive_booking_confirmations' => $user->receive_booking_confirmations,
                'receive_cancellation_confirmations' => $user->receive_cancellation_confirmations,
                'include_calendar_attachment' => $user->include_calendar_attachment,
                'roles' => [
                    [
                        'role' => Role::communityWorker()->name,
                        'clinic_id' => $clinic->id,
                    ]
                ],
                'created_at' => $user->created_at->toIso8601String(),
                'updated_at' => $user->updated_at->toIso8601String(),
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
     * Read logged in user.
     */

    public function test_guest_cannot_read_logged_in_user()
    {
        $response = $this->json('GET', '/v1/users/user');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_can_read_logged_in_user()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('GET', '/v1/users/user');

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
                'receive_booking_confirmations' => $user->receive_booking_confirmations,
                'receive_cancellation_confirmations' => $user->receive_cancellation_confirmations,
                'include_calendar_attachment' => $user->include_calendar_attachment,
                'roles' => [
                    [
                        'role' => Role::communityWorker()->name,
                        'clinic_id' => $clinic->id,
                    ]
                ],
                'created_at' => $user->created_at->toIso8601String(),
                'updated_at' => $user->updated_at->toIso8601String(),
            ]
        ]);
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
            'receive_booking_confirmations' => false,
            'receive_cancellation_confirmations' => false,
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
            'receive_booking_confirmations' => false,
            'receive_cancellation_confirmations' => false,
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
            'receive_booking_confirmations' => $communityWorker->receive_booking_confirmations,
            'receive_cancellation_confirmations' => $communityWorker->receive_cancellation_confirmations,
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
            'receive_booking_confirmations' => $communityWorker->receive_booking_confirmations,
            'receive_cancellation_confirmations' => $communityWorker->receive_cancellation_confirmations,
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
            'receive_booking_confirmations' => $communityWorker->receive_booking_confirmations,
            'receive_cancellation_confirmations' => $communityWorker->receive_cancellation_confirmations,
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
            'receive_booking_confirmations' => $communityWorker->receive_booking_confirmations,
            'receive_cancellation_confirmations' => $communityWorker->receive_cancellation_confirmations,
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
            'receive_booking_confirmations' => $communityWorker->receive_booking_confirmations,
            'receive_cancellation_confirmations' => $communityWorker->receive_cancellation_confirmations,
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
            'receive_booking_confirmations' => $communityWorker->receive_booking_confirmations,
            'receive_cancellation_confirmations' => $communityWorker->receive_cancellation_confirmations,
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
            'receive_booking_confirmations' => $communityWorker->receive_booking_confirmations,
            'receive_cancellation_confirmations' => $communityWorker->receive_cancellation_confirmations,
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
            'receive_booking_confirmations' => $communityWorker->receive_booking_confirmations,
            'receive_cancellation_confirmations' => $communityWorker->receive_cancellation_confirmations,
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
            'receive_booking_confirmations' => $communityWorker->receive_booking_confirmations,
            'receive_cancellation_confirmations' => $communityWorker->receive_cancellation_confirmations,
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
            'receive_booking_confirmations' => false,
            'receive_cancellation_confirmations' => false,
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
            'receive_booking_confirmations' => false,
            'receive_cancellation_confirmations' => false,
            'include_calendar_attachment' => false,
            'roles' => [
                [
                    'role' => Role::COMMUNITY_WORKER,
                    'clinic_id' => $clinic->id,
                ]
            ],
            'profile_picture' => static::BASE64_JPEG,
        ]);

        $data = json_decode($response->getContent(), true);
        $createdUserId = $data['data']['id'];

        $response = $this->get("/v1/users/$createdUserId/profile-picture.jpg");
        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'image/jpeg');

        $sourcePicture = base64_decode_image(static::BASE64_JPEG);
        $sourcePicture = crop_and_resize(
            $sourcePicture,
            User::PROFILE_PICTURE_WIDTH,
            User::PROFILE_PICTURE_HEIGHT
        );
        $sourcePicture = 'data:image/jpeg;base64,' . base64_encode($sourcePicture);
        $profilePictureBase64Encoded = 'data:image/jpeg;base64,' . base64_encode($response->getContent());

        $this->assertEquals($sourcePicture, $profilePictureBase64Encoded);
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

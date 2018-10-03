<?php

namespace Tests\Feature;

use App\Events\EndpointHit;
use App\Models\Audit;
use App\Models\Clinic;
use App\Models\ServiceUser;
use App\Models\User;
use App\Notifications\Sms\AccessCodeSms;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ServiceUsersTest extends TestCase
{
    /*
     * List them.
     */

    public function test_guest_cannot_list_them()
    {
        $response = $this->json('GET', '/v1/service-users');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_can_list_them()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $serviceUser = factory(ServiceUser::class)->create();

        Passport::actingAs($user);
        $response = $this->json('GET', '/v1/service-users');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $serviceUser->id,
                'name' => $serviceUser->name,
                'phone' => $serviceUser->phone,
                'email' => $serviceUser->email,
                'preferred_contact_method' => $serviceUser->preferred_contact_method,
                'created_at' => $serviceUser->created_at->format(Carbon::ISO8601),
                'updated_at' => $serviceUser->updated_at->format(Carbon::ISO8601),
            ]
        ]);
    }

    public function test_audit_created_when_listed()
    {
        $this->fakeEvents();

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $this->json('GET', '/v1/service-users');

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::READ, $event->getAction());
        });
    }

    /*
     * Read one.
     */

    public function test_guest_cannot_read_one()
    {
        $serviceUser = factory(ServiceUser::class)->create();

        $response = $this->json('GET', "/v1/service-users/{$serviceUser->id}");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_can_read_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $serviceUser = factory(ServiceUser::class)->create();

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/service-users/{$serviceUser->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $serviceUser->id,
                'name' => $serviceUser->name,
                'phone' => $serviceUser->phone,
                'email' => $serviceUser->email,
                'preferred_contact_method' => $serviceUser->preferred_contact_method,
                'created_at' => $serviceUser->created_at->format(Carbon::ISO8601),
                'updated_at' => $serviceUser->updated_at->format(Carbon::ISO8601),
            ]
        ]);
    }

    public function test_audit_created_when_read()
    {
        $this->fakeEvents();

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $serviceUser = factory(ServiceUser::class)->create();

        Passport::actingAs($user);
        $this->json('GET', "/v1/service-users/{$serviceUser->id}");

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::READ, $event->getAction());
        });
    }

    /*
     * Access code.
     */

    public function test_guest_cannot_request_access_code()
    {
        Queue::fake();

        $serviceUser = factory(ServiceUser::class)->create(['phone' => '07700000000']);

        $response = $this->json('POST', '/v1/service-users/access-code', [
            'phone' => '07799999999',
        ]);

        $response->assertStatus(Response::HTTP_OK);
        Queue::assertNotPushed(AccessCodeSms::class);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_type' => $serviceUser->getTable(),
            'notifiable_id' => $serviceUser->id,
        ]);
    }

    public function test_guest_can_request_access_code()
    {
        Queue::fake();

        $serviceUser = factory(ServiceUser::class)->create(['phone' => '07700000000']);

        $response = $this->json('POST', '/v1/service-users/access-code', [
            'phone' => $serviceUser->phone,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        Queue::assertPushed(AccessCodeSms::class);
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => $serviceUser->getTable(),
            'notifiable_id' => $serviceUser->id,
        ]);
    }

    /*
     * Token.
     */
}

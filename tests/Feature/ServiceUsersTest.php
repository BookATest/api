<?php

namespace Tests\Feature;

use App\Events\EndpointHit;
use App\Models\Appointment;
use App\Models\Audit;
use App\Models\Clinic;
use App\Models\ServiceUser;
use App\Models\User;
use App\Notifications\Sms\ServiceUser\AccessCodeSms;
use Illuminate\Http\Response;
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
                'created_at' => $serviceUser->created_at->toIso8601String(),
                'updated_at' => $serviceUser->updated_at->toIso8601String(),
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

    public function test_filter_by_name_works()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $serviceUser1 = factory(ServiceUser::class)->create(['name' => 'John Doe']);
        $serviceUser2 = factory(ServiceUser::class)->create(['name' => 'Foo Bar']);

        Passport::actingAs($user);
        $query = http_build_query(['filter[name]' => 'John']);
        $response = $this->json('GET', "/v1/service-users?$query");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment(['id' => $serviceUser1->id]);
        $response->assertJsonMissing(['id' => $serviceUser2->id]);
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
                'created_at' => $serviceUser->created_at->toIso8601String(),
                'updated_at' => $serviceUser->updated_at->toIso8601String(),
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
     * List appointments for one.
     */

    public function test_guest_cannot_list_appointments_for_one()
    {
        $serviceUser = factory(ServiceUser::class)->create();

        $response = $this->json('GET', "/v1/service-users/$serviceUser->id/appointments");

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_su_can_list_their_appointments()
    {
        /** @var \App\Models\ServiceUser $serviceUser */
        $serviceUser = factory(ServiceUser::class)->create();

        /** @var \App\Models\Appointment $appointment */
        $appointment = factory(Appointment::class)->create();
        $appointment->book($serviceUser);

        /** @var \App\Models\Appointment $anotherAppointment */
        $anotherAppointment = factory(Appointment::class)->create();

        $parameters = http_build_query(['service_user_token' => $serviceUser->generateToken()]);
        $response = $this->json('GET', "/v1/service-users/$serviceUser->id/appointments?$parameters");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'id' => $appointment->id,
            'user_id' => $appointment->user_id,
            'clinic_id' => $appointment->clinic_id,
            'is_repeating' => $appointment->appointment_schedule_id !== null,
            'service_user_id' => $serviceUser->id,
            'start_at' => $appointment->start_at->toIso8601String(),
            'booked_at' => $appointment->booked_at->toIso8601String(),
            'consented_at' => $appointment->consented_at->toIso8601String(),
            'did_not_attend' => $appointment->did_not_attend,
            'created_at' => $appointment->created_at->toIso8601String(),
            'updated_at' => $appointment->updated_at->toIso8601String(),
        ]);
        $response->assertJsonMissing(['id' => $anotherAppointment->id]);
    }

    public function test_su_cannot_list_their_appointments_with_incorrect_token()
    {
        /** @var \App\Models\ServiceUser $serviceUser */
        $serviceUser = factory(ServiceUser::class)->create();

        /** @var \App\Models\ServiceUser $anotherServiceUser */
        $anotherServiceUser = factory(ServiceUser::class)->create();

        $parameters = http_build_query(['service_user_token' => $anotherServiceUser->generateToken()]);
        $response = $this->json('GET', "/v1/service-users/$serviceUser->id/appointments?$parameters");

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
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
        $response->assertJsonStructure(['message']);
        Queue::assertPushed(AccessCodeSms::class);
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => $serviceUser->getTable(),
            'notifiable_id' => $serviceUser->id,
        ]);
    }

    /*
     * Create token.
     */

    public function test_guest_cannot_request_token()
    {
        $serviceUser = factory(ServiceUser::class)->create();

        $response = $this->json('POST', '/v1/service-users/token', [
            'phone' => $serviceUser->phone,
            'access_code' => '12345',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_guest_can_request_token()
    {
        $serviceUser = factory(ServiceUser::class)->create();

        $response = $this->json('POST', '/v1/service-users/token', [
            'phone' => $serviceUser->phone,
            'access_code' => $serviceUser->generateAccessCode(),
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['token']);
    }

    /*
     * Show from token.
     */

    public function test_guest_cannot_read_one_from_token()
    {
        $response = $this->json('GET', '/v1/service-users/token/incorrect-token');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_guest_can_read_one_from_token()
    {
        $serviceUser = factory(ServiceUser::class)->create();
        $token = $serviceUser->generateToken();

        $response = $this->json('GET', "/v1/service-users/token/{$token}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $serviceUser->id,
                'name' => $serviceUser->name,
                'phone' => $serviceUser->phone,
                'email' => $serviceUser->email,
                'preferred_contact_method' => $serviceUser->preferred_contact_method,
                'created_at' => $serviceUser->created_at->toIso8601String(),
                'updated_at' => $serviceUser->updated_at->toIso8601String(),
            ]
        ]);
    }
}

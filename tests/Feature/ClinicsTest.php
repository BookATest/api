<?php

namespace Tests\Feature;

use App\Events\EndpointHit;
use App\Models\Audit;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ClinicsTest extends TestCase
{
    /*
     * List them.
     */

    public function test_guest_can_list_them()
    {
        $clinic = factory(Clinic::class)->create();

        $response = $this->json('GET', '/v1/clinics');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $clinic->id,
                'phone' => $clinic->phone,
                'name' => $clinic->name,
                'email' => $clinic->email,
                'address_line_1' => $clinic->address_line_1,
                'address_line_2' => $clinic->address_line_2,
                'address_line_3' => $clinic->address_line_3,
                'city' => $clinic->city,
                'postcode' => $clinic->postcode,
                'directions' => $clinic->directions,
                'appointment_duration' => $clinic->appointment_duration,
                'appointment_booking_threshold' => $clinic->appointment_booking_threshold,
                'created_at' => $clinic->created_at->format(Carbon::ISO8601),
                'updated_at' => $clinic->updated_at->format(Carbon::ISO8601),
            ]
        ]);
    }

    public function test_audit_created_when_listed()
    {
        $this->fakeEvents();

        $this->json('GET', '/v1/clinics');

        Event::assertDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::READ, $event->getAction());
            return true;
        });
    }

    /*
     * Create one.
     */

    public function test_guest_cannot_create_one()
    {
        $response = $this->json('POST', '/v1/clinics');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_create_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('POST', '/v1/clinics');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_cannot_create_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        Passport::actingAs($user);
        $response = $this->json('POST', '/v1/clinics');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_oa_can_create_one()
    {
        $user = factory(User::class)->create()->makeOrganisationAdmin();

        Passport::actingAs($user);
        $response = $this->json('POST', '/v1/clinics', [
            'name' => 'Ayup Digital',
            'phone' => '01130000000',
            'email' => 'info@example.com',
            'address_line_1' => '10 Fake Street',
            'address_line_2' => null,
            'address_line_3' => null,
            'city' => 'Fake City',
            'postcode' => 'LS1 1AB',
            'directions' => 'Lorem ipsum dolar sit amet',
            'appointment_duration' => 30,
            'appointment_booking_threshold' => 120,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonFragment([
            'name' => 'Ayup Digital',
            'phone' => '01130000000',
            'email' => 'info@example.com',
            'address_line_1' => '10 Fake Street',
            'address_line_2' => null,
            'address_line_3' => null,
            'city' => 'Fake City',
            'postcode' => 'LS1 1AB',
            'directions' => 'Lorem ipsum dolar sit amet',
            'appointment_duration' => 30,
            'appointment_booking_threshold' => 120,
        ]);
    }

    public function test_audit_created_when_created()
    {
        $this->fakeEvents();

        $user = factory(User::class)->create()->makeOrganisationAdmin();

        Passport::actingAs($user);
        $this->json('POST', '/v1/clinics', [
            'name' => 'Ayup Digital',
            'phone' => '01130000000',
            'email' => 'info@example.com',
            'address_line_1' => '10 Fake Street',
            'address_line_2' => null,
            'address_line_3' => null,
            'city' => 'Fake City',
            'postcode' => 'LS1 1AB',
            'directions' => 'Lorem ipsum dolar sit amet',
            'appointment_duration' => 30,
            'appointment_booking_threshold' => 120,
        ]);

        Event::assertDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::CREATE, $event->getAction());
            return true;
        });
    }
}

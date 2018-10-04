<?php

namespace Tests\Feature;

use App\Events\EndpointHit;
use App\Models\Appointment;
use App\Models\Audit;
use App\Models\Clinic;
use App\Models\ServiceUser;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
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

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::READ, $event->getAction());
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

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::CREATE, $event->getAction());
        });
    }

    /*
     * Read one.
     */

    public function test_guest_can_read_one()
    {
        $clinic = factory(Clinic::class)->create();

        $response = $this->json('GET', "/v1/clinics/{$clinic->id}");

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

    public function test_audit_created_when_read()
    {
        $this->fakeEvents();

        $clinic = factory(Clinic::class)->create();

        $this->json('GET', "/v1/clinics/{$clinic->id}");

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

        $response = $this->json('PUT', "/v1/clinics/{$clinic->id}");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_update_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $clinic = factory(Clinic::class)->create();

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/clinics/{$clinic->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_cannot_update_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        $clinic = factory(Clinic::class)->create();

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/clinics/{$clinic->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_oa_can_update_one()
    {
        $user = factory(User::class)->create()->makeOrganisationAdmin();

        $clinic = factory(Clinic::class)->create();

        Passport::actingAs($user);
        $response = $this->json('PUT', "/v1/clinics/{$clinic->id}", [
            'name' => 'Ayup Digital',
            'phone' => '01130000000',
            'email' => 'info@example.com',
            'address_line_1' => '10 Fake Street',
            'address_line_2' => null,
            'address_line_3' => null,
            'city' => 'Fake City',
            'postcode' => 'LS1 1AB',
            'directions' => 'Lorem ipsum dolar sit amet',
            // TODO: 'appointment_duration' => 30,
            'appointment_booking_threshold' => 120,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $clinic->id,
                'name' => 'Ayup Digital',
                'phone' => '01130000000',
                'email' => 'info@example.com',
                'address_line_1' => '10 Fake Street',
                'address_line_2' => null,
                'address_line_3' => null,
                'city' => 'Fake City',
                'postcode' => 'LS1 1AB',
                'directions' => 'Lorem ipsum dolar sit amet',
                'appointment_duration' => $clinic->appointment_duration,
                'appointment_booking_threshold' => 120,
                'created_at' => $clinic->created_at->format(Carbon::ISO8601),
                'updated_at' => $clinic->updated_at->format(Carbon::ISO8601),
            ]
        ]);
    }

    public function test_audit_created_when_updated()
    {
        $this->fakeEvents();

        $user = factory(User::class)->create()->makeOrganisationAdmin();

        $clinic = factory(Clinic::class)->create();

        Passport::actingAs($user);
        $this->json('PUT', "/v1/clinics/{$clinic->id}", [
            'name' => 'Ayup Digital',
            'phone' => '01130000000',
            'email' => 'info@example.com',
            'address_line_1' => '10 Fake Street',
            'address_line_2' => null,
            'address_line_3' => null,
            'city' => 'Fake City',
            'postcode' => 'LS1 1AB',
            'directions' => 'Lorem ipsum dolar sit amet',
            // TODO: 'appointment_duration' => 30,
            'appointment_booking_threshold' => 120,
        ]);

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::UPDATE, $event->getAction());
        });
    }
    
    /*
     * Delete one.
     */

    public function test_guest_cannot_delete_one()
    {
        $clinic = factory(Clinic::class)->create();

        $response = $this->json('DELETE', "/v1/clinics/{$clinic->id}");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_delete_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $clinic = factory(Clinic::class)->create();

        Passport::actingAs($user);
        $response = $this->json('DELETE', "/v1/clinics/{$clinic->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_cannot_delete_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        $clinic = factory(Clinic::class)->create();

        Passport::actingAs($user);
        $response = $this->json('DELETE', "/v1/clinics/{$clinic->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_oa_can_delete_one()
    {
        $user = factory(User::class)->create()->makeOrganisationAdmin();

        $clinic = factory(Clinic::class)->create();

        Passport::actingAs($user);
        $response = $this->json('DELETE', "/v1/clinics/{$clinic->id}");

        $response->assertStatus(Response::HTTP_OK);
        $this->assertModelSoftDeleted($clinic);
    }

    public function test_audit_created_when_deleted()
    {
        $this->fakeEvents();

        $user = factory(User::class)->create()->makeOrganisationAdmin();

        $clinic = factory(Clinic::class)->create();

        Passport::actingAs($user);
        $this->json('DELETE', "/v1/clinics/{$clinic->id}");

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::DELETE, $event->getAction());
        });
    }

    public function test_future_appointments_cancelled_and_deleted_when_deleted()
    {
        $user = factory(User::class)->create()->makeOrganisationAdmin();

        $serviceUser = factory(ServiceUser::class)->create();
        $clinic = factory(Clinic::class)->create();
        $appointment = factory(Appointment::class)->create([
            'clinic_id' => $clinic->id,
            'start_at' => today()->addWeek(),
        ])->book($serviceUser);

        Passport::actingAs($user);
        $this->json('DELETE', "/v1/clinics/{$clinic->id}");

        $this->assertModelDeleted($appointment);
    }
}
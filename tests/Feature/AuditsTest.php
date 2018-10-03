<?php

namespace Tests\Feature;

use App\Events\EndpointHit;
use App\Models\Audit;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuditsTest extends TestCase
{
    /*
     * List them.
     */

    public function test_guest_cannot_list_them()
    {
        $response = $this->json('GET', '/v1/audits');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_list_them()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('GET', '/v1/audits');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_cannot_list_them()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        Passport::actingAs($user);
        $response = $this->json('GET', '/v1/audits');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_oa_can_list_them()
    {
        $user = factory(User::class)->create()->makeOrganisationAdmin();
        $audit = factory(Audit::class)->create();

        Passport::actingAs($user);
        $response = $this->json('GET', '/v1/audits');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $audit->id,
                'auditable_type' => $audit->auditable_type,
                'auditable_id' => $audit->auditable_id,
                'client' => optional($audit->client)->name,
                'action' => $audit->action,
                'description' => $audit->description,
                'ip_address' => $audit->ip_address,
                'user_agent' => $audit->user_agent,
                'created_at' => $audit->created_at->format(Carbon::ISO8601),
                'updated_at' => $audit->updated_at->format(Carbon::ISO8601),
            ]
        ]);
    }

    public function test_audit_created_when_listed()
    {
        $this->fakeEvents();

        $user = factory(User::class)->create()->makeOrganisationAdmin();

        Passport::actingAs($user);
        $this->json('GET', '/v1/audits');

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::READ, $event->getAction());
        });
    }

    /*
     * Read one.
     */

    public function test_guest_cannot_read_one()
    {
        $audit = factory(Audit::class)->create();

        $response = $this->json('GET', "/v1/audits/{$audit->id}");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_read_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $audit = factory(Audit::class)->create();

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/audits/{$audit->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_cannot_read_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        $audit = factory(Audit::class)->create();

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/audits/{$audit->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_oa_can_read_one()
    {
        $user = factory(User::class)->create()->makeOrganisationAdmin();

        $audit = factory(Audit::class)->create();

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/audits/{$audit->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            [
                'id' => $audit->id,
                'auditable_type' => $audit->auditable_type,
                'auditable_id' => $audit->auditable_id,
                'client' => optional($audit->client)->name,
                'action' => $audit->action,
                'description' => $audit->description,
                'ip_address' => $audit->ip_address,
                'user_agent' => $audit->user_agent,
                'created_at' => $audit->created_at->format(Carbon::ISO8601),
                'updated_at' => $audit->updated_at->format(Carbon::ISO8601),
            ]
        ]);
    }

    public function test_audit_created_when_read()
    {
        $this->fakeEvents();

        $user = factory(User::class)->create()->makeOrganisationAdmin();

        $audit = factory(Audit::class)->create();

        Passport::actingAs($user);
        $this->json('GET', "/v1/audits/{$audit->id}");

        $this->assertEventDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::READ, $event->getAction());
        });
    }
}

<?php

namespace Tests\Feature;

use App\Models\Audit;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuditsTest extends TestCase
{
    /*
     * List them.
     */

    public function test_oa_can_view_audits()
    {
        $user = factory(User::class)->create();
        $user->makeOrganisationAdmin();

        Passport::actingAs($user);

        $response = $this->json('GET', '/v1/audits');

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_cw_cannot_view_audits()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create();
        $user->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('GET', '/v1/audits');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_guest_cannot_view_audits()
    {
        $response = $this->json('GET', '/v1/audits');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_audit_is_created_when_viewing_appointment()
    {
        $user = factory(User::class)->create();
        $user->makeOrganisationAdmin();

        Passport::actingAs($user);

        $this->json('GET', '/v1/appointments', [], [
            'User-Agent' => 'PHPUnit Test'
        ]);
        $response = $this->json('GET', '/v1/audits');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'action' => 'read',
            'description' => 'Viewed all appointments',
            'user_agent' => 'PHPUnit Test',
        ]);
    }

    /*
     * Read one.
     */

    public function test_cw_cannot_read_one()
    {
        $user = factory(User::class)->create()->makeCommunityWorker(
            factory(Clinic::class)->create()
        );
        Passport::actingAs($user);

        $audit = factory(Audit::class)->create();

        $response = $this->json('GET', "/v1/audits/{$audit->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_cannot_read_one()
    {
        $user = factory(User::class)->create()->makeClinicAdmin(
            factory(Clinic::class)->create()
        );
        Passport::actingAs($user);

        $audit = factory(Audit::class)->create();

        $response = $this->json('GET', "/v1/audits/{$audit->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_oa_can_read_one()
    {
        $user = factory(User::class)->create()->makeOrganisationAdmin();
        Passport::actingAs($user);

        $audit = factory(Audit::class)->create();

        $response = $this->json('GET', "/v1/audits/{$audit->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'id' => $audit->id,
            'auditable_id' => $audit->auditable_id,
            'auditable_type' => $audit->auditable_type,
            'client' => $audit->client,
            'action' => $audit->action,
            'description' => $audit->description,
            'ip_address' => $audit->ip_address,
            'user_agent' => $audit->user_agent,
        ]);
    }
}

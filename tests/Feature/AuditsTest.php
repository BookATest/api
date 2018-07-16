<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuditsTest extends TestCase
{
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
}

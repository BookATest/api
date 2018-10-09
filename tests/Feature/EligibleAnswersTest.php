<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\TestCase;

class EligibleAnswersTest extends TestCase
{
    /*
     * List them.
     */

    public function test_guest_cannot_list_them()
    {
        $clinic = factory(Clinic::class)->create();

        $response = $this->json('GET', "/v1/clinics/$clinic->id/eligible-answers");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_list_them()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/clinics/$clinic->id/eligible-answers");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_cannot_list_them_for_another_clinic()
    {
        $clinic = factory(Clinic::class)->create();
        $anotherClinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/clinics/$anotherClinic->id/eligible-answers");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_cannot_list_them_when_not_created()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/clinics/$clinic->id/eligible-answers");

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}

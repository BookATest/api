<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ClinicsTest extends TestCase
{
    public function test_guest_cannot_create_clinic()
    {
        $response = $this->json('POST', '/v1/clinics');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_create_clinic()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create();
        $user->makeCommunityWorker($clinic);

        Passport::actingAs($user);

        $response = $this->json('POST', '/v1/clinics');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_cannot_create_clinic()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create();
        $user->makeClinicAdmin($clinic);

        Passport::actingAs($user);

        $response = $this->json('POST', '/v1/clinics');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_oa_can_create_clinic()
    {
        $user = factory(User::class)->create();
        $user->makeOrganisationAdmin();
        $clinicData = $this->getClinicPostData();

        Passport::actingAs($user);

        $response = $this->json('POST', '/v1/clinics', $clinicData);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonFragment([
            'name' => $clinicData['name'],
            'phone' => $clinicData['phone'],
            'email' => $clinicData['email'],
            'address_line_1' => $clinicData['address_line_1'],
            'address_line_2' => null,
            'address_line_3' => null,
            'city' => $clinicData['city'],
            'postcode' => $clinicData['postcode'],
            'directions' => $clinicData['directions'],
            'appointment_duration' => Setting::getValue(Setting::DEFAULT_APPOINTMENT_DURATION),
            'appointment_booking_threshold' => Setting::getValue(Setting::DEFAULT_APPOINTMENT_BOOKING_THRESHOLD),
        ]);
    }

    public function test_oa_cannot_create_clinic_without_required_fields()
    {
        $user = factory(User::class)->create();
        $user->makeOrganisationAdmin();

        Passport::actingAs($user);

        $response = $this->json('POST', '/v1/clinics');

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Helper method for test cases.
     *
     * @return array
     */
    protected function getClinicPostData(): array
    {
        return [
            'name' => $this->faker->company,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->safeEmail,
            'address_line_1' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'postcode' => $this->faker->postcode,
            'directions' => 'Turn left then it is on your right'
        ];
    }
}

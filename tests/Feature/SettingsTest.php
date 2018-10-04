<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    /*
     * List them.
     */

    public function test_guest_can_list_them()
    {
        $response = $this->json('GET', '/v1/settings');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                'default_appointment_booking_threshold' => Setting::getValue('default_appointment_booking_threshold'),
                'default_appointment_duration' => Setting::getValue('default_appointment_duration'),
                'default_notification_message' => Setting::getValue('default_notification_message'),
                'default_notification_subject' => Setting::getValue('default_notification_subject'),
                'language' => (array)Setting::getValue('language'),
                'logo_file_id' => Setting::getValue('logo_file_id'),
                'name' => Setting::getValue('name'),
                'primary_colour' => Setting::getValue('primary_colour'),
                'secondary_colour' => Setting::getValue('secondary_colour'),
            ]
        ]);
    }

    /*
     * Update them.
     */

    public function test_guest_cannot_update_them()
    {
        $response = $this->json('PUT', '/v1/settings');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_update_them()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('PUT', '/v1/settings');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_ca_cannot_update_them()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeClinicAdmin($clinic);

        Passport::actingAs($user);
        $response = $this->json('PUT', '/v1/settings');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_oa_can_update_them()
    {
        $user = factory(User::class)->create()->makeOrganisationAdmin();

        Passport::actingAs($user);
        $response = $this->json('PUT', '/v1/settings', [
            'default_appointment_booking_threshold' => 100,
            'default_appointment_duration' => 10,
            'default_notification_message' => 'Lorem ipsum dolar sit amet.',
            'default_notification_subject' => 'Test Subject',
            'language' => [
                'booking_questions_help_text' => 'Test 1',
                'booking_notification_help_text' => 'Test 2',
                'booking_enter_details_help_text' => 'Test 3',
                'booking_find_location_help_text' => 'Test 4',
                'booking_appointment_overview_help_text' => 'Test 5',
            ],
            'logo_file_id' => null,
            'name' => 'PHPUnit Test',
            'primary_colour' => '#ffffff',
            'secondary_colour' => '#000000',
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                'default_appointment_booking_threshold' => 100,
                'default_appointment_duration' => 10,
                'default_notification_message' => 'Lorem ipsum dolar sit amet.',
                'default_notification_subject' => 'Test Subject',
                'language' => [
                    'booking_questions_help_text' => 'Test 1',
                    'booking_notification_help_text' => 'Test 2',
                    'booking_enter_details_help_text' => 'Test 3',
                    'booking_find_location_help_text' => 'Test 4',
                    'booking_appointment_overview_help_text' => 'Test 5',
                ],
                'logo_file_id' => null,
                'name' => 'PHPUnit Test',
                'primary_colour' => '#ffffff',
                'secondary_colour' => '#000000',
            ]
        ]);
    }
}

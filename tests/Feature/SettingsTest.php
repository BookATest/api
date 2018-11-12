<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    const BASE64_PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

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
                'language' => Setting::getValue('language'),
                'name' => Setting::getValue('name'),
                'primary_colour' => Setting::getValue('primary_colour'),
                'secondary_colour' => Setting::getValue('secondary_colour'),
                'styles' => '',
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
            'language' => [
                'booking_questions_help_text' => 'Test 1',
                'booking_notification_help_text' => 'Test 2',
                'booking_enter_details_help_text' => 'Test 3',
                'booking_find_location_help_text' => 'Test 4',
                'booking_appointment_overview_help_text' => 'Test 5',
            ],
            'name' => 'PHPUnit Test',
            'primary_colour' => '#ffffff',
            'secondary_colour' => '#000000',
            'styles' => '* { display: none; }',
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                'default_appointment_booking_threshold' => 100,
                'default_appointment_duration' => 10,
                'language' => [
                    'booking_questions_help_text' => 'Test 1',
                    'booking_notification_help_text' => 'Test 2',
                    'booking_enter_details_help_text' => 'Test 3',
                    'booking_find_location_help_text' => 'Test 4',
                    'booking_appointment_overview_help_text' => 'Test 5',
                ],
                'name' => 'PHPUnit Test',
                'primary_colour' => '#ffffff',
                'secondary_colour' => '#000000',
                'styles' => '* { display: none; }',
            ]
        ]);
    }

    public function test_logo_can_be_updated()
    {
        $user = factory(User::class)->create()->makeOrganisationAdmin();

        Passport::actingAs($user);
        $response = $this->json('PUT', '/v1/settings', [
            'default_appointment_booking_threshold' => 100,
            'default_appointment_duration' => 10,
            'language' => [
                'booking_questions_help_text' => 'Test 1',
                'booking_notification_help_text' => 'Test 2',
                'booking_enter_details_help_text' => 'Test 3',
                'booking_find_location_help_text' => 'Test 4',
                'booking_appointment_overview_help_text' => 'Test 5',
            ],
            'name' => 'PHPUnit Test',
            'primary_colour' => '#ffffff',
            'secondary_colour' => '#000000',
            'logo' => static::BASE64_PNG,
            'styles' => '',
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $response = $this->get('/v1/settings/logo.png');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertEquals(
            base64_decode_image(static::BASE64_PNG),
            $response->getContent()
        );
    }

    /*
     * View logo.
     */

    public function test_guest_can_view_logo()
    {
        $response = $this->get('/v1/settings/logo.png');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertEquals(
            Storage::disk('local')->get('placeholders/organisation-logo.png'),
            $response->getContent()
        );
    }

    /*
     * View custom CSS.
     */

    public function test_guest_can_view_styles()
    {
        $response = $this->get('/v1/settings/styles.css');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'text/css; charset=UTF-8');
        $content = $response->getContent();
        $this->assertEquals('', $content);
    }
}

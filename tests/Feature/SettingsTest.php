<?php

namespace Tests\Feature;

use App\Models\Clinic;
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
        $response->assertJsonStructure([
            'data' => [
                'default_appointment_booking_threshold',
                'default_appointment_duration',
                'language' => [
                    'home' => ['title', 'content'],
                    'make-booking' => [
                        'introduction' => ['title', 'content'],
                        'questions' => ['title', 'content'],
                        'location' => ['title', 'content'],
                        'clinics' => ['title', 'content', 'ineligible'],
                        'appointments' => ['title', 'content'],
                        'user-details' => ['title', 'content'],
                        'consent' => ['title', 'content'],
                        'overview' => ['title', 'content'],
                        'confirmation' => ['title', 'content'],
                    ],
                    'list-bookings' => [
                        'access-code' => ['title', 'content'],
                        'token' => ['title', 'content'],
                        'appointments' => ['title', 'content', 'disclaimer'],
                        'cancel' => ['title', 'content'],
                        'cancelled' => ['title', 'content'],
                        'token-expired' => ['title', 'content'],
                    ],
                ],
                'name',
                'email',
                'phone',
                'primary_colour',
                'secondary_colour',
                'styles',
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
                'home' => ['title' => 'Lorem', 'content' => 'Lorem'],
                'make-booking' => [
                    'introduction' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'questions' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'location' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'clinics' => ['title' => 'Lorem', 'content' => 'Lorem', 'ineligible' => 'Lorem'],
                    'appointments' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'user-details' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'consent' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'overview' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'confirmation' => ['title' => 'Lorem', 'content' => 'Lorem'],
                ],
                'list-bookings' => [
                    'access-code' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'token' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'appointments' => ['title' => 'Lorem', 'content' => 'Lorem', 'disclaimer' => 'Lorem'],
                    'cancel' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'cancelled' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'token-expired' => ['title' => 'Lorem', 'content' => 'Lorem'],
                ],
            ],
            'name' => 'PHPUnit Test',
            'email' => 'info@example.com',
            'phone' => '00000000000',
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
                    'home' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'make-booking' => [
                        'introduction' => ['title' => 'Lorem', 'content' => 'Lorem'],
                        'questions' => ['title' => 'Lorem', 'content' => 'Lorem'],
                        'location' => ['title' => 'Lorem', 'content' => 'Lorem'],
                        'clinics' => ['title' => 'Lorem', 'content' => 'Lorem', 'ineligible' => 'Lorem'],
                        'appointments' => ['title' => 'Lorem', 'content' => 'Lorem'],
                        'user-details' => ['title' => 'Lorem', 'content' => 'Lorem'],
                        'consent' => ['title' => 'Lorem', 'content' => 'Lorem'],
                        'overview' => ['title' => 'Lorem', 'content' => 'Lorem'],
                        'confirmation' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    ],
                    'list-bookings' => [
                        'access-code' => ['title' => 'Lorem', 'content' => 'Lorem'],
                        'token' => ['title' => 'Lorem', 'content' => 'Lorem'],
                        'appointments' => ['title' => 'Lorem', 'content' => 'Lorem', 'disclaimer' => 'Lorem'],
                        'cancel' => ['title' => 'Lorem', 'content' => 'Lorem'],
                        'cancelled' => ['title' => 'Lorem', 'content' => 'Lorem'],
                        'token-expired' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    ],
                ],
                'name' => 'PHPUnit Test',
                'email' => 'info@example.com',
                'phone' => '00000000000',
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
                'home' => ['title' => 'Lorem', 'content' => 'Lorem'],
                'make-booking' => [
                    'introduction' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'questions' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'location' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'clinics' => ['title' => 'Lorem', 'content' => 'Lorem', 'ineligible' => 'Lorem'],
                    'appointments' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'user-details' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'consent' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'overview' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'confirmation' => ['title' => 'Lorem', 'content' => 'Lorem'],
                ],
                'list-bookings' => [
                    'access-code' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'token' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'appointments' => ['title' => 'Lorem', 'content' => 'Lorem', 'disclaimer' => 'Lorem'],
                    'cancel' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'cancelled' => ['title' => 'Lorem', 'content' => 'Lorem'],
                    'token-expired' => ['title' => 'Lorem', 'content' => 'Lorem'],
                ],
            ],
            'name' => 'PHPUnit Test',
            'email' => 'info@example.com',
            'phone' => '00000000000',
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

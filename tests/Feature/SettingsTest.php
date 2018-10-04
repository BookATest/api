<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Http\Response;
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
}

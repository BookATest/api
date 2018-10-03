<?php

namespace Tests\Feature;

use App\Events\EndpointHit;
use App\Models\Audit;
use App\Models\Clinic;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
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

        Event::assertDispatched(EndpointHit::class, function (EndpointHit $event) {
            $this->assertEquals(Audit::READ, $event->getAction());
            return true;
        });
    }
}

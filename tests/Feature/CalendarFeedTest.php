<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CalendarFeedTest extends TestCase
{
    public function test_cw_can_refresh_their_own_calendar_feed_token()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create();
        $user->makeCommunityWorker($clinic);

        Passport::actingAs($user);

        $response = $this->json('PUT', "/v1/users/{$user->id}/calendar-feed-token");
        $user = $user->fresh();

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(['calendar_feed_token' => $user->calendar_feed_token]);
    }

    public function test_cw_cannot_refresh_another_users_calendar_feed_token()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create();
        $user->makeCommunityWorker($clinic);

        $response = $this->json('PUT', "/v1/users/{$user->id}/calendar-feed-token");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_guest_cannot_refresh_a_users_calendar_feed_token()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create();
        $user->makeCommunityWorker($clinic);
        $differentUser = factory(User::class)->create();
        $differentUser->makeCommunityWorker($clinic);

        Passport::actingAs($differentUser);

        $response = $this->json('PUT', "/v1/users/{$user->id}/calendar-feed-token");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}

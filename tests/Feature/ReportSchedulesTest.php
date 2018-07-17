<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\ReportSchedule;
use App\Models\ReportType;
use App\Models\User;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ReportSchedulesTest extends TestCase
{
    public function test_guest_cannot_view_report_schedules_for_user()
    {
        $user = factory(User::class)->create();

        $response = $this->json('GET', "/v1/users/{$user->id}/report-schedules");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_cannot_view_another_users_report_schedule()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $anotherUser= factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($anotherUser);

        $response = $this->json('GET', "/v1/users/{$user->id}/report-schedules");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_cw_can_view_their_report_schedule()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $reportSchedule = ReportSchedule::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'report_type_id' => ReportType::getIdFor(ReportType::COUNT_TESTING_TYPES),
            'repeat_type' => ReportSchedule::MONTHLY,
        ]);

        Passport::actingAs($user);

        $response = $this->json('GET', "/v1/users/{$user->id}/report-schedules");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'report_type' => $reportSchedule->reportType->name,
            'repeat_type' => $reportSchedule->repeat_type,
        ]);
    }
}

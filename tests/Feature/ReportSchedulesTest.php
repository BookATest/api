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
    /*
     * List them.
     */

    public function test_guest_cannot_list_them()
    {
        $response = $this->json('GET', '/v1/report-schedules');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_can_list_their_own()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $reportSchedule = factory(ReportSchedule::class)->create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
        ]);

        Passport::actingAs($user);
        $response = $this->json('GET', '/v1/report-schedules');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'id' => $reportSchedule->id,
            'user_id' => $reportSchedule->user_id,
            'clinic_id' => $reportSchedule->clinic_id,
            'report_type' => $reportSchedule->reportType->name,
            'repeat_type' => $reportSchedule->repeat_type,
            'created_at' => $reportSchedule->created_at->toIso8601String(),
            'updated_at' => $reportSchedule->updated_at->toIso8601String(),
        ]);
    }

    public function test_cw_cannot_list_someone_elses()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $reportSchedule = factory(ReportSchedule::class)->create();

        Passport::actingAs($user);
        $response = $this->json('GET', '/v1/report-schedules');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonMissing(['id' => $reportSchedule->id]);
    }

    /*
     * Create one.
     */

    public function test_guest_cannot_create_one()
    {
        $response = $this->json('POST', '/v1/report-schedules');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_can_create_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('POST', '/v1/report-schedules', [
            'clinic_id' => null,
            'report_type' => ReportType::GENERAL_EXPORT,
            'repeat_type' => ReportSchedule::WEEKLY,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonFragment([
            'user_id' => $user->id,
            'clinic_id' => null,
            'report_type' => ReportType::GENERAL_EXPORT,
            'repeat_type' => ReportSchedule::WEEKLY,
        ]);
    }

    public function test_cw_can_create_one_for_their_clinic()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('POST', '/v1/report-schedules', [
            'clinic_id' => $clinic->id,
            'report_type' => ReportType::GENERAL_EXPORT,
            'repeat_type' => ReportSchedule::WEEKLY,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonFragment([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'report_type' => ReportType::GENERAL_EXPORT,
            'repeat_type' => ReportSchedule::WEEKLY,
        ]);
    }

    public function test_cw_cannot_create_one_for_another_clinic()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        Passport::actingAs($user);
        $response = $this->json('POST', '/v1/report-schedules', [
            'clinic_id' => factory(Clinic::class)->create()->id,
            'report_type' => ReportType::GENERAL_EXPORT,
            'repeat_type' => ReportSchedule::WEEKLY,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /*
     * Read one.
     */

    public function test_guest_cannot_read_one()
    {
        $reportSchedule = factory(ReportSchedule::class)->create();

        $response = $this->json('GET', "/v1/report-schedules/$reportSchedule->id");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_can_read_one()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $reportSchedule = factory(ReportSchedule::class)->create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
        ]);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/report-schedules/$reportSchedule->id");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'id' => $reportSchedule->id,
            'user_id' => $reportSchedule->user_id,
            'clinic_id' => $reportSchedule->clinic_id,
            'report_type' => $reportSchedule->reportType->name,
            'repeat_type' => $reportSchedule->repeat_type,
            'created_at' => $reportSchedule->created_at->toIso8601String(),
            'updated_at' => $reportSchedule->updated_at->toIso8601String(),
        ]);
    }

    public function test_cw_cannot_read_someone_elses()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $reportSchedule = factory(ReportSchedule::class)->create();

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/report-schedules/$reportSchedule->id");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}

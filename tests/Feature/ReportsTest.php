<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\File;
use App\Models\Report;
use App\Models\ReportType;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    /*
     * List them.
     */

    public function test_guest_cannot_list_them()
    {
        $response = $this->json('GET', '/v1/reports');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_can_list_their_own_reports()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);

        $file = factory(File::class)->create([
            'filename' => 'test_report.xlsx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
        $report = factory(Report::class)->create([
            'user_id' => $user->id,
            'file_id' => $file->id,
        ]);
        Storage::cloud()->put('reports/test_report.xlsx', 'fake content');

        Passport::actingAs($user);
        $response = $this->json('GET', '/v1/reports');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'id' => $report->id,
            'user_id' => $report->user_id,
            'clinic_id' => $report->clinic_id,
            'type' => $report->reportType->name,
            'start_at' => $report->start_at->toDateString(),
            'end_at' => $report->end_at->toDateString(),
            'created_at' => $report->created_at->toIso8601String(),
            'updated_at' => $report->updated_at->toIso8601String(),
        ]);
    }

    /*
     * Create one.
     */

    public function test_guest_cannot_create_one()
    {
        $response = $this->json('POST', '/v1/reports');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_can_create_one()
    {
        Storage::fake();

        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $reportType = ReportType::findByName(ReportType::GENERAL_EXPORT);

        Passport::actingAs($user);
        $response = $this->json('POST', '/v1/reports', [
            'clinic_id' => null,
            'type' => $reportType->name,
            'start_at' => today()->startOfWeek()->toDateString(),
            'end_at' => today()->endOfWeek()->toDateString(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonFragment([
            'user_id' => $user->id,
            'clinic_id' => null,
            'type' => $reportType->name,
            'start_at' => today()->startOfWeek()->toDateString(),
            'end_at' => today()->endOfWeek()->toDateString(),
        ]);

        $file = Report::query()->firstOrFail()->file;
        Storage::cloud()->assertExists($file->path());
    }

    /*
     * Read one.
     */

    public function test_guest_cannot_read_one()
    {
        $report = factory(Report::class)->create();

        $response = $this->json('GET', "/v1/reports/$report->id");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_can_view_own_report()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $report = factory(Report::class)->create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
        ]);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/reports/$report->id");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'id' => $report->id,
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'type' => $report->reportType->name,
            'start_at' => $report->start_at->toDateString(),
            'end_at' => $report->end_at->toDateString(),
            'created_at' => $report->created_at->toIso8601String(),
            'updated_at' => $report->updated_at->toIso8601String(),
        ]);
    }

    public function test_cw_cannot_view_another_cw_report()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $report = factory(Report::class)->create([
            'clinic_id' => $clinic->id,
        ]);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/reports/$report->id");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_cw_can_view_their_global_report()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $report = factory(Report::class)->create([
            'user_id' => $user->id,
            'clinic_id' => null,
        ]);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/reports/$report->id");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'id' => $report->id,
            'user_id' => $user->id,
            'clinic_id' => null,
            'type' => $report->reportType->name,
            'start_at' => $report->start_at->toDateString(),
            'end_at' => $report->end_at->toDateString(),
            'created_at' => $report->created_at->toIso8601String(),
            'updated_at' => $report->updated_at->toIso8601String(),
        ]);
    }

    public function test_oa_can_view_global_report()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeOrganisationAdmin($clinic);
        $report = factory(Report::class)->create([
            'user_id' => $user->id,
            'clinic_id' => null,
        ]);

        Passport::actingAs($user);
        $response = $this->json('GET', "/v1/reports/$report->id");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment([
            'id' => $report->id,
            'user_id' => $user->id,
            'clinic_id' => null,
            'type' => $report->reportType->name,
            'start_at' => $report->start_at->toDateString(),
            'end_at' => $report->end_at->toDateString(),
            'created_at' => $report->created_at->toIso8601String(),
            'updated_at' => $report->updated_at->toIso8601String(),
        ]);
    }

    /*
     * Delete one.
     */

    public function test_guest_cannot_delete_one()
    {
        $report = factory(Report::class)->create();

        $response = $this->json('DELETE', "/v1/reports/$report->id");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cw_can_delete_their_own()
    {
        $clinic = factory(Clinic::class)->create();
        $user = factory(User::class)->create()->makeCommunityWorker($clinic);
        $report = factory(Report::class)->create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
        ]);
        $file = $report->file;

        Passport::actingAs($user);
        $response = $this->json('DELETE', "/v1/reports/$report->id");

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseMissing($report->getTable(), ['id' => $report->id]);
        $this->assertDatabaseMissing($file->getTable(), ['id' => $file->id]);
    }
}

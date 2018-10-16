<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\File;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ReportsTest extends TestCase
{
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
}

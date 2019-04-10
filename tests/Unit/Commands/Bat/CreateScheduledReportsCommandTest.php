<?php

namespace Tests\Unit\Commands\Bat;

use App\Console\Commands\Bat\CreateScheduledReportsCommand;
use App\Models\Report;
use App\Models\ReportSchedule;
use App\Notifications\Email\CommunityWorker\ReportGeneratedEmail;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CreateScheduledReportsCommandTest extends TestCase
{
    public function test_weekly_scheduled_report_created()
    {
        factory(ReportSchedule::class)->create([
            'repeat_type' => ReportSchedule::WEEKLY,
        ]);
        $startAt = now()->startOfWeek();

        CarbonImmutable::setTestNow($startAt);
        $this->artisan(CreateScheduledReportsCommand::class);

        $this->assertDatabaseHas('reports', [
            'start_at' => $startAt->toDateTimeString(),
        ]);
    }

    public function test_monthly_scheduled_report_created()
    {
        factory(ReportSchedule::class)->create([
            'repeat_type' => ReportSchedule::MONTHLY,
        ]);
        $startAt = now()->startOfMonth();

        CarbonImmutable::setTestNow($startAt);
        $this->artisan(CreateScheduledReportsCommand::class);

        $this->assertDatabaseHas('reports', [
            'start_at' => $startAt->toDateTimeString(),
        ]);
    }

    public function test_no_report_created_when_not_due()
    {
        factory(ReportSchedule::class)->create([
            'repeat_type' => ReportSchedule::WEEKLY,
        ]);
        $startAt = now()->startOfWeek()->addDay();

        CarbonImmutable::setTestNow($startAt);
        $this->artisan(CreateScheduledReportsCommand::class);

        $this->assertEquals(0, Report::query()->count());
    }

    public function test_notification_sent_out_when_report_generated()
    {
        Queue::fake();

        factory(ReportSchedule::class)->create([
            'repeat_type' => ReportSchedule::WEEKLY,
        ]);
        $startAt = now()->startOfWeek();

        CarbonImmutable::setTestNow($startAt);
        $this->artisan(CreateScheduledReportsCommand::class);

        Queue::assertPushed(ReportGeneratedEmail::class);
    }
}

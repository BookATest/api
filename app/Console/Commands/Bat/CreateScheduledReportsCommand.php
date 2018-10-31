<?php

namespace App\Console\Commands\Bat;

use App\Models\Report;
use App\Models\ReportSchedule;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CreateScheduledReportsCommand extends Command
{
    const MONDAY = 1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bat:create-scheduled-reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create reports from report schedules';

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * @var int
     */
    protected $successful = 0;

    /**
     * @var int
     */
    protected $failed = 0;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->count = ReportSchedule::query()->count();

        // Output the number of report schedules.
        $this->line("Generating reports for $this->count report schedules...");

        ReportSchedule::query()
            ->with('user', 'clinic', 'reportType')
            ->chunk(200, function (Collection $reportSchedules) {
                $reportSchedules->each(function (ReportSchedule $reportSchedule) {
                    // Output creating message.
                    $this->line("generating report for report schedule [$reportSchedule->id]...");

                    switch ($reportSchedule->repeat_type) {
                        case ReportSchedule::WEEKLY:
                            $this->handleWeekly($reportSchedule);
                            break;
                        case ReportSchedule::MONTHLY:
                            $this->handleMonthly($reportSchedule);
                            break;
                    }
                });
            });

        if ($this->failed > 0) {
            $this->error("Generated reports for $this->successful report schedules. Failed generating reports for $this->failed report schedules.");
        } else {
            $this->info("Generated reports for $this->successful report schedules.");
        }
    }

    /**
     * @param \App\Models\ReportSchedule $reportSchedule
     */
    protected function handleWeekly(ReportSchedule $reportSchedule)
    {
        // Skip if not a Monday.
        if (now()->dayOfWeekIso !== static::MONDAY) {
            // Output skipped message.
            $this->info("Report not due for report schedule [$reportSchedule->id]");

            return;
        }

        try {
            // Attempt to create.
            Report::createAndUpload(
                $reportSchedule->user,
                $reportSchedule->clinic,
                $reportSchedule->reportType,
                now()->startOfWeek(),
                now()->endOfWeek()
            );

            // Output success message.
            $this->info("Generated report for report schedule [$reportSchedule->id]");

            // Increment successful.
            $this->successful++;
        } catch (\Throwable $exception) {
            // Output error message.
            $this->error("Failed to generate report for report schedule [$reportSchedule->id]");

            // Increment failed.
            $this->failed++;
        }
    }

    /**
     * @param \App\Models\ReportSchedule $reportSchedule
     */
    protected function handleMonthly(ReportSchedule $reportSchedule)
    {
        // Skip if not the first day of the month.
        if (now()->day !== 1) {
            // Output skipped message.
            $this->info("Report not due for report schedule [$reportSchedule->id]");

            return;
        }

        try {
            // Attempt to create.
            Report::createAndUpload(
                $reportSchedule->user,
                $reportSchedule->clinic,
                $reportSchedule->reportType,
                now()->startOfMonth(),
                now()->endOfMonth()
            );

            // Output success message.
            $this->info("Generated report for report schedule [$reportSchedule->id]");

            // Increment successful.
            $this->successful++;
        } catch (\Throwable $exception) {
            // Output error message.
            $this->error("Failed to generate report for report schedule [$reportSchedule->id]");

            // Increment failed.
            $this->failed++;
        }
    }
}

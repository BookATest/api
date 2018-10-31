<?php

namespace App\Models;

use App\Models\Mutators\ReportScheduleMutators;
use App\Models\Relationships\ReportScheduleRelationships;
use App\ReportGenerators\ReportGeneratorFactory;

class ReportSchedule extends Model
{
    use ReportScheduleMutators;
    use ReportScheduleRelationships;

    const WEEKLY = 'weekly';
    const MONTHLY = 'monthly';

    /**
     * @return \App\Models\Report
     */
    public function createReport(): Report
    {
        $file = File::create([
            'filename' => "{$this->repeat_type}_{$report->start_at->toDateString()}-{$report->end_at->toDateString()}.xlsx",
            'mime_type' => File::MIME_XLSX,
        ]);

        $report = new Report([
            'file_id' => $file->id,
            'user_id' => $this->user_id,
            'clinic_id' => $this->clinic_id,
            'report_type_id' => $this->report_type_id,
        ]);

        switch ($this->repeat_type) {
            case static::WEEKLY:
                $report->start_at = now()->startOfWeek();
                $report->end_at = now()->endOfWeek();
                break;
            case static::MONTHLY:
                $report->start_at = now()->startOfMonth();
                $report->end_at = now()->endOfMonth();
                break;
        }

        $report->save();

        $file->upload(
            ReportGeneratorFactory::for($report)->generate()
        );

        return $report;
    }
}

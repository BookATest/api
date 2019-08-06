<?php

namespace App\Models;

use App\Models\Mutators\ReportScheduleMutators;
use App\Models\Relationships\ReportScheduleRelationships;
use App\ReportGenerators\ReportGeneratorFactory;
use Illuminate\Support\Facades\Date;

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
        $report = new Report([
            'user_id' => $this->user_id,
            'clinic_id' => $this->clinic_id,
            'report_type_id' => $this->report_type_id,
        ]);

        $file = File::create([
            'filename' => "{$this->repeat_type}_{$report->start_at->toDateString()}-{$report->end_at->toDateString()}.xlsx",
            'mime_type' => File::MIME_XLSX,
        ]);

        $report->file_id = $file->id;

        switch ($this->repeat_type) {
            case static::WEEKLY:
                $report->start_at = Date::now()->startOfWeek();
                $report->end_at = Date::now()->endOfWeek();
                break;
            case static::MONTHLY:
                $report->start_at = Date::now()->startOfMonth();
                $report->end_at = Date::now()->endOfMonth();
                break;
        }

        $report->save();

        $file->upload(
            ReportGeneratorFactory::for($report)->generate()
        );

        return $report;
    }
}

<?php

namespace App\Models;

use App\Models\Mutators\ReportMutators;
use App\Models\Relationships\ReportRelationships;
use App\ReportGenerators\ReportGeneratorFactory;
use Carbon\Carbon;

class Report extends Model
{
    use ReportMutators;
    use ReportRelationships;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_at' => 'date',
        'end_at' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Called just before the model is deleted.
     *
     * @param \App\Models\Model $report
     */
    protected function onDeleted(Model $report)
    {
        $report->file->delete();
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\Clinic|null $clinic
     * @param \App\Models\ReportType $reportType
     * @param \Carbon\Carbon $startAt
     * @param \Carbon\Carbon $endAt
     * @return \App\Models\Report
     * @throws \App\Exceptions\InvalidReportTypeException
     */
    public static function createAndUpload(
        User $user,
        ?Clinic $clinic,
        ReportType $reportType,
        Carbon $startAt,
        Carbon $endAt
    ): self {
        // Create the file.
        /** @var \App\Models\File $file */
        $file = File::create([
            'filename' => "{$reportType->name}_{$startAt->toDateString()}-{$endAt->toDateString()}.xlsx",
            'mime_type' => File::MIME_XLSX,
        ]);

        // Create the report model.
        /** @var \App\Models\Report $report */
        $report = Report::create([
            'user_id' => $user->id,
            'file_id' => $file->id,
            'clinic_id' => $clinic->id ?? null,
            'report_type_id' => $reportType->id,
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);

        // Generate the report.
        $file->upload(
            ReportGeneratorFactory::for($report)->generate()
        );

        return $report;
    }
}

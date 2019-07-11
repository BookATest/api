<?php

namespace Tests\Unit\ReportGenerators;

use App\Models\Appointment;
use App\Models\Report;
use App\ReportGenerators\GeneralExportGenerator;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class GeneralExportGeneratorTest extends TestCase
{
    public function test_global_export_correct()
    {
        $appointment = factory(Appointment::class)->create([
            'start_at' => Date::today(),
        ]);

        $report = factory(Report::class)->create([
            'clinic_id' => null,
            'start_at' => Date::today()->subMonth(),
            'end_at' => Date::today()->addMonth(),
        ]);

        $generator = new GeneralExportGenerator($report);
        $report = $generator->generate();
        $sheet = $this->xlsxToArray($report);

        $this->assertEquals(2, count($sheet));
        $this->assertEquals([
            'ID',
            'User ID',
            'User Name',
            'Clinic ID',
            'Clinic Name',
            'Service User ID',
            'Service User Name',
            'Did Not Attend?',
            'Start Date',
            'Date Booked',
            'Date Consented',
        ], $sheet[0]);
        $this->assertEquals([
            $appointment->id,
            $appointment->user->id,
            $appointment->user->full_name,
            $appointment->clinic->id,
            $appointment->clinic->name,
            null,
            null,
            null,
            $appointment->start_at->toIso8601String(),
            null,
            null,
        ], $sheet[1]);
    }

    /**
     * @param string $xlsx
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @return array
     */
    protected function xlsxToArray(string $xlsx): array
    {
        $tempFileId = uuid();

        Storage::disk('temp')->put($tempFileId, $xlsx);

        $spreadsheet = IOFactory::load(storage_path("temp/$tempFileId"));
        $sheet = $spreadsheet->getActiveSheet()->toArray();

        Storage::disk('temp')->delete($tempFileId);

        return $sheet;
    }
}

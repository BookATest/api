<?php

declare(strict_types=1);

namespace App\ReportGenerators;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GeneralExportGenerator extends ReportGenerator
{
    use ExportsXlsx;

    const COLUMN_ID = 1;
    const COLUMN_USER_ID = 2;
    const COLUMN_USER_NAME = 3;
    const COLUMN_CLINIC_ID = 4;
    const COLUMN_CLINIC_NAME = 5;
    const COLUMN_SERVICE_USER_ID = 6;
    const COLUMN_SERVICE_USER_NAME = 7;
    const COLUMN_DID_NOT_ATTEND = 8;
    const COLUMN_START_DATE = 9;
    const COLUMN_DATE_BOOKED = 10;
    const COLUMN_DATE_CONSENTED = 11;

    /**
     * Generate the report and return the contents.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @return string
     */
    public function generate(): string
    {
        // Create the spreadsheet.
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add rows to rhe XLSX.
        $this->addHeaderRow($sheet);
        $this->addAppointmentRows($sheet);

        return $this->save($spreadsheet);
    }

    /**
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @return \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    protected function addHeaderRow(Worksheet $sheet): Worksheet
    {
        $headerRow = [
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
        ];

        foreach ($headerRow as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
        }

        return $sheet;
    }

    /**
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @return \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    protected function addAppointmentRows(Worksheet $sheet): Worksheet
    {
        $row = 2;

        Appointment::query()
            ->with('user', 'clinic', 'serviceUser')
            ->whereBetween('appointments.start_at', [$this->report->start_at, $this->report->end_at])
            ->when($this->report->clinic, function (Builder $query) {
                $query->where('appointments.clinic_id', $this->report->clinic->id);
            })
            ->chunk(200, function (Collection $appointments) use ($sheet, &$row) {
                $appointments->each(function (Appointment $appointment) use ($sheet, &$row) {
                    $sheet->setCellValueByColumnAndRow(static::COLUMN_ID, $row, $appointment->id);
                    $sheet->setCellValueByColumnAndRow(static::COLUMN_USER_ID, $row, $appointment->user->id);
                    $sheet->setCellValueByColumnAndRow(static::COLUMN_USER_NAME, $row, $appointment->user->full_name);
                    $sheet->setCellValueByColumnAndRow(static::COLUMN_CLINIC_ID, $row, optional($appointment->clinic)->id);
                    $sheet->setCellValueByColumnAndRow(static::COLUMN_CLINIC_NAME, $row, optional($appointment->clinic)->name);
                    $sheet->setCellValueByColumnAndRow(static::COLUMN_SERVICE_USER_ID, $row, optional($appointment->serviceUser)->id);
                    $sheet->setCellValueByColumnAndRow(static::COLUMN_SERVICE_USER_NAME, $row, optional($appointment->serviceUser)->name);
                    $sheet->setCellValueByColumnAndRow(static::COLUMN_DID_NOT_ATTEND, $row, $appointment->did_not_attend);
                    $sheet->setCellValueByColumnAndRow(static::COLUMN_START_DATE, $row, $appointment->start_at->toIso8601String());
                    $sheet->setCellValueByColumnAndRow(static::COLUMN_DATE_BOOKED, $row, optional($appointment->booked_at)->toIso8601String());
                    $sheet->setCellValueByColumnAndRow(static::COLUMN_DATE_CONSENTED, $row, optional($appointment->consented_at)->toIso8601String());

                    $row++;
                });
            });

        return $sheet;
    }
}

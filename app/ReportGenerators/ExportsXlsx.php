<?php

namespace App\ReportGenerators;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

trait ExportsXlsx
{
    /**
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @return string
     */
    protected function save(Spreadsheet $spreadsheet): string
    {
        $uuid = uuid();

        $writer = new Xlsx($spreadsheet);

        $writer->save(storage_path("temp/$uuid"));
        $contents = Storage::disk('temp')->get($uuid);
        Storage::disk('temp')->delete($uuid);

        return $contents;
    }
}

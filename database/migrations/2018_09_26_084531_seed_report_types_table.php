<?php

declare(strict_types=1);

use App\Database\Migrations\MigrationSeeder;
use App\Models\ReportType;
use Illuminate\Support\Facades\DB;

class SeedReportTypesTable extends MigrationSeeder
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $this->addRecord(ReportType::GENERAL_EXPORT);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::table('report_types')->truncate();
    }

    /**
     * @param array $args
     */
    protected function addRecord(...$args)
    {
        list($name) = $args;

        DB::table('report_types')->insert([
            'id' => uuid(),
            'name' => $name,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);
    }
}

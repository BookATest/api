<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReportTypeIdColumnToReportSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_schedules', function (Blueprint $table) {
            $table->uuid('report_type_id')->after('clinic_id');
            $table->foreign('report_type_id')->references('id')->on('report_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_schedules', function (Blueprint $table) {
            $table->dropForeign(['report_type_id']);
            $table->dropColumn('report_type_id');
        });
    }
}

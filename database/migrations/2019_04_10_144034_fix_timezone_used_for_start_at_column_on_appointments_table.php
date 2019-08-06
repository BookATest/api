<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixTimezoneUsedForStartAtColumnOnAppointmentsTable extends Migration
{
    const DST_START = '2019-03-31 00:00:00';
    const DST_END = '2019-10-27 00:00:00';

    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::table('appointments')
            ->whereBetween('start_at', [static::DST_START, static::DST_END])
            ->update([
                'start_at' => DB::raw('DATE_ADD(`start_at`, INTERVAL 1 HOUR)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::table('appointments')
            ->whereBetween('start_at', [static::DST_START, static::DST_END])
            ->update([
                'start_at' => DB::raw('DATE_SUB(`start_at`, INTERVAL 1 HOUR)'),
            ]);
    }
}

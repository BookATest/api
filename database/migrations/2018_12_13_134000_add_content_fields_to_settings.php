<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddContentFieldsToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->where('key', '=', 'language')->update([
            'value' => DB::raw('JSON_SET(`value`, \'$."make-booking".clinics.ineligible\', "Lorem ipsum")'),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('key', '=', 'language')->update([
            'value' => DB::raw('JSON_REMOVE(`value`, \'$."make-booking".clinics.ineligible\')'),
        ]);
    }
}

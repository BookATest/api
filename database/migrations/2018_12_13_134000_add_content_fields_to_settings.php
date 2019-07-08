<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddContentFieldsToSettings extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::table('settings')->where('key', '=', 'language')->update([
            'value' => DB::raw('JSON_SET(`value`, \'$."make-booking".clinics.ineligible\', "Lorem ipsum")'),
        ]);
        DB::table('settings')->where('key', '=', 'language')->update([
            'value' => DB::raw('JSON_SET(`value`, \'$."make-booking"."no-consent"\', JSON_OBJECT("title", "Lorem ipsum", "content", "Lorem ipsum"))'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::table('settings')->where('key', '=', 'language')->update([
            'value' => DB::raw('JSON_REMOVE(`value`, \'$."make-booking".clinics.ineligible\')'),
        ]);
        DB::table('settings')->where('key', '=', 'language')->update([
            'value' => DB::raw('JSON_REMOVE(`value`,\'$."make-booking"."no-consent"\')'),
        ]);
    }
}

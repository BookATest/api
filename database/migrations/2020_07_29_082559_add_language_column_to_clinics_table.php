<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddLanguageColumnToClinicsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->json('language')
                ->nullable()
                ->after('send_dna_follow_ups');
        });

        DB::table('clinics')->update([
            'language' => json_encode([
                'make-booking' => [
                    'appointments' => [
                        'title' => null,
                        'content' => null,
                    ],
                ],
            ]),
        ]);

        Schema::table('clinics', function (Blueprint $table) {
            $table->json('language')
                ->nullable(false)
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('language');
        });
    }
}

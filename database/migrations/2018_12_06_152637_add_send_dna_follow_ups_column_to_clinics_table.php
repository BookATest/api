<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSendDnaFollowUpsColumnToClinicsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->boolean('send_dna_follow_ups')->default(false)->after('send_cancellation_confirmations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('send_dna_follow_ups');
        });
    }
}

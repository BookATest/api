<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSendCancellationConfirmationsColumnToClinicsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->boolean('send_cancellation_confirmations')->default(false)->after('appointment_booking_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('send_cancellation_confirmations');
        });
    }
}

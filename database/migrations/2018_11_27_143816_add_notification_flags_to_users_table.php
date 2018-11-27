<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotificationFlagsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('receive_booking_confirmations')->default(true)->after('display_phone');
            $table->boolean('receive_cancellation_confirmations')->default(true)->after('receive_booking_confirmations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('receive_booking_confirmations');
            $table->dropColumn('receive_cancellation_confirmations');
        });
    }
}

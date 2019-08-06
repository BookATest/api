<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id', 'users');
            $table->foreignUuid('clinic_id', 'clinics');
            $table->foreignUuid('appointment_schedule_id', 'appointment_schedules', 'id', true);
            $table->foreignUuid('service_user_id', 'service_users', 'id', true);
            $table->boolean('did_not_attend')->nullable();
            $table->dateTime('start_at');
            $table->timestamp('booked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}

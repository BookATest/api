<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('user_id', 'users');
            $table->foreignId('clinic_id', 'clinics');
            $table->unsignedInteger('appointment_schedule_id')->nullable();
            $table->foreign('appointment_schedule_id')->references('id')->on('appointment_schedules');
            $table->uuid('service_user_uuid')->nullable();
            $table->foreign('service_user_uuid')->references('uuid')->on('service_users');
            $table->boolean('did_not_attend')->nullable();
            $table->dateTime('start_at');
            $table->timestamp('booked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}

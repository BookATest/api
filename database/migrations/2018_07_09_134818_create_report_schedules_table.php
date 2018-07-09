<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('user_id', 'users');
            $table->unsignedInteger('clinic_id')->nullable();
            $table->foreign('clinic_id')->references('id')->on('clinics');
            $table->enum('repeat_type', ['weekly', 'monthly']);
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
        Schema::dropIfExists('report_schedules');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLatAndLonColumnsToClinicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->decimal('lat', 9, 6)->nullable()->after('postcode');
            $table->decimal('lon', 9, 6)->nullable()->after('lat');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('lat');
            $table->dropColumn('lon');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClientInformationColumnsToAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->unsignedInteger('client_id')->nullable()->after('auditable_id');
            $table->ipAddress('ip_address')->after('description');
            $table->string('user_agent', 1000)->after('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropColumn('user_agent');
            $table->dropColumn('ip_address');
            $table->dropColumn('client_id');
        });
    }
}

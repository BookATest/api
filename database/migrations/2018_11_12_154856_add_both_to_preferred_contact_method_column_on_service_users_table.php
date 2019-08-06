<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddBothToPreferredContactMethodColumnOnServiceUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $enums = ['email', 'phone', 'both'];
        $enumsString = "'" . implode("','", $enums) . "'";

        DB::statement("ALTER TABLE `service_users` MODIFY COLUMN `preferred_contact_method` ENUM({$enumsString}) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $enums = ['email', 'phone'];
        $enumsString = "'" . implode("','", $enums) . "'";

        DB::statement("ALTER TABLE `service_users` MODIFY COLUMN `preferred_contact_method` ENUM({$enumsString}) NOT NULL");
    }
}

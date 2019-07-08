<?php

declare(strict_types=1);

use App\Database\Migrations\MigrationSeeder;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class SeedRolesTable extends MigrationSeeder
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $this->addRecord(Role::COMMUNITY_WORKER);
        $this->addRecord(Role::CLINIC_ADMIN);
        $this->addRecord(Role::ORGANISATION_ADMIN);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::table('roles')->truncate();
    }

    /**
     * @param array $args
     */
    protected function addRecord(...$args)
    {
        list($name) = $args;

        DB::table('roles')->insert([
            'id' => uuid(),
            'name' => $name,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);
    }
}

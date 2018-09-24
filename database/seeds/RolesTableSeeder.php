<?php

use App\Models\Role;

class RolesTableSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->addRecord(Role::COMMUNITY_WORKER);
        $this->addRecord(Role::CLINIC_ADMIN);
        $this->addRecord(Role::ORGANISATION_ADMIN);

        $this->db->table('roles')->insert($this->records);
    }

    /**
     * @param array $args
     *
     * @return void
     */
    protected function addRecord(...$args)
    {
        list($name) = $args;

        $this->records[] = [
            'id' => uuid(),
            'name' => $name,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ];
    }
}

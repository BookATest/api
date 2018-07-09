<?php

class RolesTableSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->addRecord('Community Worker');
        $this->addRecord('Clinic Admin');
        $this->addRecord('Organisation Admin');

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
            'name' => $name,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ];
    }
}

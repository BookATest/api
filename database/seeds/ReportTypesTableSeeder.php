<?php

class ReportTypesTableSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->addRecord('Report Type 1');
        $this->addRecord('Report Type 2');
        $this->addRecord('Report Type 3');

        $this->db->table('report_types')->insert($this->records);
    }

    /**
     * @param array $args
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

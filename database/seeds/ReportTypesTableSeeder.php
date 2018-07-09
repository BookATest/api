<?php

use App\Models\ReportType;

class ReportTypesTableSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->addRecord(ReportType::COUNT_APPOINTMENTS_AVAILABLE);
        $this->addRecord(ReportType::COUNT_APPOINTMENTS_BOOKED);
        $this->addRecord(ReportType::COUNT_DID_NOT_ATTEND);
        $this->addRecord(ReportType::COUNT_TESTING_TYPES);

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

<?php

use App\Database\Migrations\MigrationSeeder;
use Illuminate\Support\Facades\DB;

class AddStylesRowToSettingsTable extends MigrationSeeder
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->addRecord('styles', '');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('key', 'styles')->delete();
    }

    /**
     * @param array $args
     *
     * @return void
     */
    protected function addRecord(...$args)
    {
        list($key, $value) = $args;

        DB::table('settings')->insert([
            'key' => $key,
            'value' => json_encode($value),
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);
    }
}

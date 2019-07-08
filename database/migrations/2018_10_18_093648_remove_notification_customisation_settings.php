<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class RemoveNotificationCustomisationSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')
            ->whereIn('key', ['default_notification_message', 'default_notification_subject'])
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->addRecord('default_notification_subject', 'Your Appointment');
        $this->addRecord('default_notification_message', 'Your appointment has been booked at {{time}}');
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

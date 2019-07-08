<?php

declare(strict_types=1);

use App\Database\Migrations\MigrationSeeder;
use Illuminate\Support\Facades\DB;

class SeedSettingsTable extends MigrationSeeder
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $this->addRecord('name', 'Organisation Name');
        $this->addRecord('logo_file_id', null);
        $this->addRecord('primary_colour', '#3a4975');
        $this->addRecord('secondary_colour', '#56b5b2');
        $this->addRecord('default_appointment_duration', 30);
        $this->addRecord('default_appointment_booking_threshold', 120);
        $this->addRecord('default_notification_subject', 'Your Appointment');
        $this->addRecord('default_notification_message', 'Your appointment has been booked at {{time}}');
        $this->addRecord('language', [
            'booking_questions_help_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque dictum.',
            'booking_find_location_help_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque dictum.',
            'booking_enter_details_help_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque dictum.',
            'booking_notification_help_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque dictum.',
            'booking_appointment_overview_help_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque dictum.',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::table('settings')->truncate();
    }

    /**
     * @param array $args
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

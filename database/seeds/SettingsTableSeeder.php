<?php

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * @var array
     */
    protected $settings = [];

    /**
     * Run the database seeds.
     *
     * @param \Illuminate\Database\DatabaseManager $db
     *
     * @return void
     */
    public function run(DatabaseManager $db)
    {
        $this->addSetting('name', 'Organisation Name');
        $this->addSetting('logo_file_id', 0);
        $this->addSetting('primary_colour', '#3a4975');
        $this->addSetting('secondary_colour', '#56b5b2');
        $this->addSetting('default_appointment_duration', 30);
        $this->addSetting('default_appointment_booking_threshold', 120);
        $this->addSetting('default_notification_subject', 'Your Appointment');
        $this->addSetting('default_notification_message', 'Your appointment has been booked at {{time}}');
        $this->addSetting('language', [
            'booking_questions_help_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque dictum.',
            'booking_find_location_help_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque dictum.',
            'booking_enter_details_help_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque dictum.',
            'booking_notification_help_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque dictum.',
            'booking_appointment_overview_help_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque dictum.',
        ]);

        $db->table('settings')->insert($this->settings);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function addSetting(string $key, $value)
    {
        $now = now();

        $this->settings[] = [
            'key' => $key,
            'value' => json_encode($value),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}

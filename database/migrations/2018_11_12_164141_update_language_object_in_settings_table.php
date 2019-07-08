<?php

use App\Database\Migrations\MigrationSeeder;
use Illuminate\Support\Facades\DB;

class UpdateLanguageObjectInSettingsTable extends MigrationSeeder
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $language = json_encode([
            'home' => [
                'title' => 'Welcome to Book A Test',
                'content' => 'Lorem ipsum dolar sit amet.',
            ],
            'make-booking' => [
                'introduction' => [
                    'title' => 'Book test here',
                    'content' => 'Lorem ipsum dolar sit amet.',
                ],
                'questions' => [
                    'title' => 'What are you looking for?',
                    'content' => 'Lorem ipsum dolar sit amet.',
                ],
                'location' => [
                    'title' => 'Find location',
                    'content' => 'Lorem ipsum dolar sit amet.',
                ],
                'clinics' => [
                    'title' => 'Find location',
                    'content' => 'Lorem ipsum dolar sit amet.',
                ],
                'appointments' => [
                    'title' => 'Date / Time',
                    'content' => 'Lorem ipsum dolar sit amet.',
                ],
                'user-details' => [
                    'title' => 'Enter details',
                    'content' => 'Lorem ipsum dolar sit amet.',
                ],
                'consent' => [
                    'title' => 'Give your consent',
                    'content' => 'Lorem ipsum dolar sit amet.',
                ],
                'overview' => [
                    'title' => 'Appointment overview',
                    'content' => 'Lorem ipsum dolar sit amet.',
                ],
                'confirmation' => [
                    'title' => 'Appointment booked!',
                    'content' => 'Lorem ipsum dolar sit amet.',
                ],
            ],
            'list-bookings' => [
                'access-code' => [
                    'title' => 'My appointments',
                    'content' => 'Lorem ipsum dolar sit amet.',
                ],
                'token' => [
                    'title' => 'My appointments',
                    'content' => 'Lorem ipsum dolar sit amet.',
                ],
                'appointments' => [
                    'title' => 'Booked appointments',
                    'content' => 'Lorem ipsum dolar sit amet.',
                    'disclaimer' => 'Lorem ipsum dolar sit amet.',
                ],
                'cancel' => [
                    'title' => 'Cancel',
                    'content' => 'Lorem ipsum dolar sit amet.',
                ],
                'cancelled' => [
                    'title' => 'Appointment cancelled',
                    'content' => 'Lorem ipsum dolar sit amet.',
                ],
                'token-expired' => [
                    'title' => 'Session expired',
                    'content' => 'Lorem ipsum dolar sit amet.',
                ],
            ],
        ]);

        DB::table('settings')
            ->where('key', 'language')
            ->update(['value' => $language]);

        $this->addRecord('phone', '00000000000');
        $this->addRecord('email', 'info@example.com');
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $language = json_encode([
            'booking_appointment_overview_help_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque dictum.',
            'booking_enter_details_help_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque dictum.',
            'booking_find_location_help_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque dictum.',
            'booking_notification_help_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque dictum.',
            'booking_questions_help_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque dictum.',
        ]);

        DB::table('settings')
            ->where('key', 'language')
            ->update(['value' => $language]);

        DB::table('settings')->where('key', 'phone')->delete();
        DB::table('settings')->where('key', 'email')->delete();
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

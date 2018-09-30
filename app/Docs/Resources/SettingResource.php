<?php

namespace App\Docs\Resources;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SettingResource extends BaseResource
{
    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema
     */
    public static function resource(): Schema
    {
        return Schema::object()->properties(
            Schema::integer('default_appointment_booking_threshold'),
            Schema::integer('default_appointment_duration'),
            Schema::string('default_notification_message'),
            Schema::string('default_notification_subject'),
            Schema::object('language')->properties(
                Schema::string('booking_questions_help_text'),
                Schema::string('booking_notification_help_text'),
                Schema::string('booking_enter_details_help_text'),
                Schema::string('booking_find_location_help_text'),
                Schema::string('booking_appointment_overview_help_text')
            ),
            Schema::string('logo_file_id')->format(Schema::UUID),
            Schema::string('name'),
            Schema::string('primary_colour'),
            Schema::string('secondary_colour')
        );
    }
}

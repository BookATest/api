<?php

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\SettingResource;
use App\Docs\Responses;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class Settings
{
    /**
     * Settings constructor.
     */
    protected function __construct()
    {
        // Prevent instantiation.
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function index(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(SettingResource::show())
            ),
        ];

        return Operation::get(...$responses)
            ->security([])
            ->summary('List all the organisation settings')
            ->description('**Permission:** `Open`')
            ->operationId('settings.index')
            ->tags(Tags::settings()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function update(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(SettingResource::show())
            ),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required(
                    'default_appointment_booking_threshold',
                    'default_appointment_duration',
                    'language',
                    'name',
                    'primary_colour',
                    'secondary_colour'
                )
                ->properties(
                    Schema::integer('default_appointment_booking_threshold'),
                    Schema::integer('default_appointment_duration'),
                    Schema::object('language')
                        ->required(
                            'booking_questions_help_text',
                            'booking_notification_help_text',
                            'booking_enter_details_help_text',
                            'booking_find_location_help_text',
                            'booking_appointment_overview_help_text'
                        )
                        ->properties(
                            Schema::string('booking_questions_help_text'),
                            Schema::string('booking_notification_help_text'),
                            Schema::string('booking_enter_details_help_text'),
                            Schema::string('booking_find_location_help_text'),
                            Schema::string('booking_appointment_overview_help_text')
                        ),
                    Schema::string('name'),
                    Schema::string('primary_colour'),
                    Schema::string('secondary_colour'),
                    Schema::string('logo')->description('Base64 encoded PNG.')
                )
        );

        return Operation::put(...$responses)
            ->requestBody($requestBody)
            ->summary('Update all of the organisation settings')
            ->description('**Permission:** `Organisation Admin`')
            ->operationId('settings.update')
            ->tags(Tags::settings()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function logo(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::create('image/png', Schema::string()->format(Schema::BINARY))
            ),
        ];

        return Operation::get(...$responses)
            ->summary('Get the organisation\'s logo')
            ->description('**Permission:** `Open`')
            ->operationId('settings.logo')
            ->tags(Tags::settings()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function styles(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::create('text/css', Schema::string())
            ),
        ];

        return Operation::get(...$responses)
            ->summary('Get the custom CSS')
            ->description('**Permission:** `Open`')
            ->operationId('settings.styles')
            ->tags(Tags::settings()->name);
    }
}

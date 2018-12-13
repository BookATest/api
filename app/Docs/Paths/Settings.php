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
                    'email',
                    'phone',
                    'primary_colour',
                    'secondary_colour'
                )
                ->properties(
                    Schema::integer('default_appointment_booking_threshold'),
                    Schema::integer('default_appointment_duration'),
                    Schema::object('language')
                        ->required(
                            'home',
                            'make-booking',
                            'list-bookings'
                        )
                        ->properties(
                            Schema::object('home')
                                ->required('title', 'content')
                                ->properties(
                                    Schema::string('title'),
                                    Schema::string('content')
                                ),
                            Schema::object('make-booking')
                                ->required(
                                    'clinics',
                                    'consent',
                                    'location',
                                    'overview',
                                    'questions',
                                    'appointments',
                                    'confirmation',
                                    'introduction',
                                    'user-details'
                                )
                                ->properties(
                                    Schema::object('clinics')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')
                                        ),
                                    Schema::object('consent')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')
                                        ),
                                    Schema::object('location')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')
                                        ),
                                    Schema::object('overview')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')
                                        ),
                                    Schema::object('questions')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')
                                        ),
                                    Schema::object('appointments')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')
                                        ),
                                    Schema::object('confirmation')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')
                                        ),
                                    Schema::object('introduction')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')
                                        ),
                                    Schema::object('user-details')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')
                                        )
                                ),
                            Schema::object('list-bookings')
                                ->required(
                                    'token',
                                    'cancel',
                                    'cancelled',
                                    'access-code',
                                    'appointments',
                                    'token-expired'
                                )
                                ->properties(
                                    Schema::object('token')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')
                                        ),
                                    Schema::object('cancel')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')
                                        ),
                                    Schema::object('cancelled')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')
                                        ),
                                    Schema::object('access-code')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')
                                        ),
                                    Schema::object('appointments')
                                        ->required('title', 'content', 'disclaimer')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content'),
                                            Schema::string('disclaimer')
                                        ),
                                    Schema::object('token-expired')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')
                                        )
                                )
                        ),
                    Schema::string('name'),
                    Schema::string('email'),
                    Schema::string('phone'),
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

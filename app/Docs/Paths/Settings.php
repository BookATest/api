<?php

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\SettingResource;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
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
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function index(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::json()->schema(SettingResource::show())
                ),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->noSecurity()
            ->summary('List all the organisation settings')
            ->description('**Permission:** `Open`')
            ->operationId('settings.index')
            ->tags(Tags::settings());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function update(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::json()->schema(SettingResource::show())
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
                        ->required('home', 'make-booking', 'list-bookings')
                        ->properties(
                            Schema::object('home')
                                ->required('title', 'content')
                                ->properties(
                                    Schema::string('title'),
                                    Schema::string('content')->nullable()
                                ),
                            Schema::object('make-booking')
                                ->required(
                                    'clinics',
                                    'consent',
                                    'no-consent',
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
                                        ->required('title', 'content', 'ineligible')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')->nullable(),
                                            Schema::string('ineligible')
                                        ),
                                    Schema::object('consent')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')->nullable()
                                        ),
                                    Schema::object('no-consent')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')->nullable()
                                        ),
                                    Schema::object('location')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')->nullable()
                                        ),
                                    Schema::object('overview')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')->nullable()
                                        ),
                                    Schema::object('questions')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')->nullable()
                                        ),
                                    Schema::object('appointments')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')->nullable()
                                        ),
                                    Schema::object('confirmation')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')->nullable()
                                        ),
                                    Schema::object('introduction')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')->nullable()
                                        ),
                                    Schema::object('user-details')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')->nullable()
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
                                            Schema::string('content')->nullable()
                                        ),
                                    Schema::object('cancel')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')->nullable()
                                        ),
                                    Schema::object('cancelled')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')->nullable()
                                        ),
                                    Schema::object('access-code')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')->nullable()
                                        ),
                                    Schema::object('appointments')
                                        ->required('title', 'content', 'disclaimer')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')->nullable(),
                                            Schema::string('disclaimer')
                                        ),
                                    Schema::object('token-expired')
                                        ->required('title', 'content')
                                        ->properties(
                                            Schema::string('title'),
                                            Schema::string('content')->nullable()
                                        )
                                )
                        ),
                    Schema::string('name'),
                    Schema::string('email'),
                    Schema::string('phone'),
                    Schema::string('primary_colour'),
                    Schema::string('secondary_colour'),
                    Schema::string('styles'),
                    Schema::string('logo')->description('Base64 encoded PNG.')
                )
        );

        return Operation::put()
            ->responses(...$responses)
            ->requestBody($requestBody)
            ->summary('Update all of the organisation settings')
            ->description('**Permission:** `Organisation Admin`')
            ->operationId('settings.update')
            ->tags(Tags::settings());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function logo(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::png()->schema(
                        Schema::string()->format(Schema::FORMAT_BINARY)
                    )
                ),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->summary('Get the organisation\'s logo')
            ->description('**Permission:** `Open`')
            ->operationId('settings.logo')
            ->tags(Tags::settings());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function styles(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::create()
                        ->mediaType('text/css')
                        ->schema(Schema::string())
                ),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->summary('Get the custom CSS')
            ->description('**Permission:** `Open`')
            ->operationId('settings.styles')
            ->tags(Tags::settings());
    }
}

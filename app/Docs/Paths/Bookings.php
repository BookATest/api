<?php

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\AppointmentResource;
use App\Docs\Resources\ClinicResource;
use App\Docs\Responses;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class Bookings
{
    /**
     * Bookings constructor.
     */
    protected function __construct()
    {
        // Prevent initialisation.
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function store(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(AppointmentResource::show())
            ),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required('appointment_id', 'service_user', 'answers', 'notification')
                ->properties(
                    Schema::string('appointment_id')->format(Schema::UUID),
                    Schema::object('service_user')
                        ->required('name', 'phone', 'email', 'preferred_contact_method')
                        ->properties(
                            Schema::string('name'),
                            Schema::string('phone'),
                            Schema::string('email'),
                            Schema::string('preferred_contact_method')->enum('email', 'phone')
                        ),
                    Schema::array('answers')->items(Schema::object()
                        ->required('question_id', 'answer')
                        ->properties(
                            Schema::string('question_id')->format(Schema::UUID),
                            Schema::string('answer')
                        ))
                )
        );

        $description = <<<EOT
**Permission:** `Open`

***

Validation is in place to ensure only eligible service users can make a booking for this appointment. You should always first check which clinics the user is eligible at.
EOT;

        return Operation::post(...$responses)
            ->security([])
            ->requestBody($requestBody)
            ->summary('Make a booking for the service user')
            ->description($description)
            ->operationId('bookings.store')
            ->tags(Tags::bookings()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function eligibleClinics(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(ClinicResource::all())
            ),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required('postcode', 'location', 'answers')
                ->properties(
                    Schema::string('postcode'),
                    Schema::object('location')
                        ->required('lat', 'lon')
                        ->properties(
                            Schema::number('lat'),
                            Schema::number('lon')
                        ),
                    Schema::array('answers')->items(
                        Schema::object()
                            ->required('question_id', 'answer')
                            ->properties(
                                Schema::string('question_id')->format(Schema::UUID),
                                Schema::string('answer')
                            )
                    )
                )
        );

        $description = <<<EOT
**Permission:** `Open`

***

This endpoint should be called at first instance to ensure the service user only attempts to book at a clinic that they are eligible for.

A postcode OR a location must be provided to order the clinics by distance.
EOT;

        return Operation::post(...$responses)
            ->security([])
            ->requestBody($requestBody)
            ->summary('Check which clinics the service user is eligible for')
            ->description($description)
            ->operationId('bookings.eligible-clinics')
            ->tags(Tags::bookings()->name);
    }
}

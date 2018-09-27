<?php

namespace App\Docs\Paths;

use App\Docs\Requests;
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
        // TODO
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function eligibleClinics(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(ClinicResource::list())
            ),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required('postcode', 'answers')
                ->properties(
                    Schema::string('postcode'),
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

        return Operation::post(...$responses)
            ->requestBody($requestBody)
            ->summary('Create a new appointment')
            ->description(<<<EOT
**Permission:** `Open`

***

This endpoint should be called at first instance to ensure the service user only attempts to book at a clinic that they are eligible for.

A postcode must be provided to order the clinics by distance.
EOT
            )
            ->operationId('bookings.eligible-clinics')
            ->tags(Tags::bookings()->name);
    }
}

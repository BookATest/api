<?php

namespace App\Docs\Paths;

use App\Docs\Resources\AppointmentResource;
use App\Docs\Responses;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;

class Appointments
{
    /**
     * Appointments constructor.
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
                MediaType::json(
                    AppointmentResource::list()
                )
            )
        ];

        return Operation::get(...$responses)
            ->summary('List all appointments')
            ->description('**Permission:** `Community Worker`')
            ->operationId('appointments.index')
            ->tags(Tags::appointments()->name);
    }
}

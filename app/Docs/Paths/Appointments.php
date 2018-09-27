<?php

namespace App\Docs\Paths;

use App\Docs\Resources\AppointmentResource;
use App\Docs\Responses;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

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
                MediaType::json(AppointmentResource::list()),
                MediaType::create(MediaType::TEXT_CALENDAR, Schema::string())
            )
        ];

        $parameters = [
            Parameter::query('user_id', Schema::string()->format(Schema::UUID))
                ->description('Comma separated user IDs'),
            Parameter::query('clinic_id', Schema::string()->format(Schema::UUID))
                ->description('Comma separated clinic IDs'),
            Parameter::query('service_user_id', Schema::string()->format(Schema::UUID))
                ->description('Comma separated service user IDs'),
            Parameter::query('format', Schema::string()->enum('json', 'ics')->default('json'))
                ->description('The desired format for the response'),
        ];

        return Operation::get(...$responses)
            ->parameters(...$parameters)
            ->summary('List all appointments')
            ->description('**Permission:** `Community Worker`')
            ->operationId('appointments.index')
            ->tags(Tags::appointments()->name);
    }
}

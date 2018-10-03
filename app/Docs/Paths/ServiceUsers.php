<?php

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\ServiceUserResource;
use App\Docs\Responses;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class ServiceUsers
{
    /**
     * ServiceUsers constructor.
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
                MediaType::json(ServiceUserResource::list())
            ),
        ];
        $parameters = [
            Parameter::query('filter[id]', Schema::string())
                ->description('Comma separated appointment IDs'),
            Parameter::query('sort', Schema::string()->default('name'))
                ->description('The field to sort the results by [`name`]'),
        ];

        return Operation::get(...$responses)
            ->parameters(...$parameters)
            ->summary('List all service users')
            ->description('**Permission:** `Community Worker`')
            ->operationId('service-users.index')
            ->tags(Tags::serviceUsers()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function show(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(ServiceUserResource::show())
            ),
        ];
        $parameters = [
            Parameter::path('service_user', Schema::string()->format(Schema::UUID))
                ->description('The service user ID')
                ->required(),
        ];

        return Operation::get(...$responses)
            ->parameters(...$parameters)
            ->summary('Get a specific service user')
            ->description('**Permission:** `Community Worker`')
            ->operationId('service-users.show')
            ->tags(Tags::serviceUsers()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function accessCode(): Operation
    {
        $responses = [
            Responses::http201(
                MediaType::json(Schema::object()->properties(
                    Schema::string('message')->example('If the number provided has been used to make a booking, then an access code has been sent.')
                ))
            ),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required('phone')
                ->properties(Schema::string('phone'))
        );

        return Operation::post(...$responses)
            ->requestBody($requestBody)
            ->summary('Request a one-time access code used to request a token')
            ->description('**Permission:** `Open`')
            ->operationId('service-users.access-code')
            ->tags(Tags::serviceUsers()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function token(): Operation
    {
        $responses = [
            Responses::http201(
                MediaType::json(ServiceUserResource::showWithAppointments())
            ),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required('access_code')
                ->properties(Schema::string('access_code'))
        );

        return Operation::post(...$responses)
            ->requestBody($requestBody)
            ->summary('Request a short lived token to manage their appointments')
            ->description('**Permission:** `Open`')
            ->operationId('service-users.token')
            ->tags(Tags::serviceUsers()->name);
    }
}

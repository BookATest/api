<?php

declare(strict_types=1);

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\AppointmentResource;
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
            Parameter::query('filter[name]', Schema::string())
                ->description('The service user\'s name'),
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
                MediaType::json(Schema::object()->properties(
                    Schema::string('token')
                ))
            ),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required('phone', 'access_code')
                ->properties(
                    Schema::string('phone'),
                    Schema::string('access_code')
                )
        );

        return Operation::post(...$responses)
            ->requestBody($requestBody)
            ->summary('Request a short lived token to manage their appointments')
            ->description('**Permission:** `Open`')
            ->operationId('service-users.token')
            ->tags(Tags::serviceUsers()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function showToken(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(ServiceUserResource::show())
            ),
        ];
        $parameters = [
            Parameter::path('token', Schema::string())
                ->description('The token requested with the access code')
                ->required(),
        ];

        return Operation::get(...$responses)
            ->security([])
            ->parameters(...$parameters)
            ->summary('Get the service user for the token')
            ->description('**Permission:** `Open`')
            ->operationId('service-users.token.show')
            ->tags(Tags::serviceUsers()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function appointments(): Operation
    {
        $description = <<<'EOT'
**Permission:** `Service User`
* View all their appointments
EOT;

        $responses = [
            Responses::http200(
                MediaType::json(AppointmentResource::list())
            ),
        ];
        $parameters = [
            Parameter::path('service_user', Schema::string()->format(Schema::UUID))
                ->description('The service user ID')
                ->required(),
            Parameter::query('service_user_token', Schema::string())
                ->description('The short lived service user token')
                ->required(),
            Parameter::query('filter[id]', Schema::string())
                ->description('Comma separated appointment IDs'),
            Parameter::query('filter[user_id]', Schema::string())
                ->description('Comma separated user IDs'),
            Parameter::query('filter[clinic_id]', Schema::string())
                ->description('Comma separated clinic IDs'),
            Parameter::query('filter[available]', Schema::boolean())
                ->description('If only available appointments should be returned. If the user is not authenticated, then they can only see appointments which are available'),
            Parameter::query('append', Schema::string())
                ->description('Comma separated fields to append [`user_first_name`, `user_last_name`, `user_email`, `user_phone`]'),
            Parameter::query('sort', Schema::string()->default('start_at'))
                ->description('The field to sort the results by [`start_at`]'),
        ];

        return Operation::get(...$responses)
            ->security([])
            ->parameters(...$parameters)
            ->summary('List all appointments for the service user')
            ->description($description)
            ->operationId('service-users.appointments.index')
            ->tags(Tags::appointments()->name, Tags::serviceUsers()->name);
    }
}

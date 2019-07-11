<?php

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\AppointmentResource;
use App\Docs\Resources\ServiceUserResource;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
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
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function index(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::json()->schema(ServiceUserResource::list())
                ),
        ];
        $parameters = [
            Parameter::query()
                ->name('filter[id]')
                ->schema(Schema::string())
                ->description('Comma separated appointment IDs'),
            Parameter::query()
                ->name('filter[name]')
                ->schema(Schema::string())
                ->description('The service user\'s name'),
            Parameter::query()
                ->name('sort')
                ->schema(Schema::string()->default('name'))
                ->description('The field to sort the results by [`name`]'),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('List all service users')
            ->description('**Permission:** `Community Worker`')
            ->operationId('service-users.index')
            ->tags(Tags::serviceUsers());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function show(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::json()->schema(ServiceUserResource::show())
                ),
        ];
        $parameters = [
            Parameter::path()
                ->name('service_user')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('The service user ID')
                ->required(),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('Get a specific service user')
            ->description('**Permission:** `Community Worker`')
            ->operationId('service-users.show')
            ->tags(Tags::serviceUsers());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function accessCode(): Operation
    {
        $responses = [
            Response::created()
                ->content(
                    MediaType::json()->schema(
                        Schema::object()
                            ->properties(
                                Schema::string('message')->example('If the number provided has been used to make a booking, then an access code has been sent.')
                            )
                    )
                ),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required('phone')
                ->properties(Schema::string('phone'))
        );

        return Operation::post()
            ->responses(...$responses)
            ->requestBody($requestBody)
            ->summary('Request a one-time access code used to request a token')
            ->description('**Permission:** `Open`')
            ->operationId('service-users.access-code')
            ->tags(Tags::serviceUsers());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function token(): Operation
    {
        $responses = [
            Response::created()
                ->content(
                    MediaType::json()->schema(
                        Schema::object()
                            ->properties(Schema::string('token'))
                    )
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

        return Operation::post()
            ->responses(...$responses)
            ->requestBody($requestBody)
            ->summary('Request a short lived token to manage their appointments')
            ->description('**Permission:** `Open`')
            ->operationId('service-users.token')
            ->tags(Tags::serviceUsers());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function showToken(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::json()->schema(ServiceUserResource::show())
                ),
        ];
        $parameters = [
            Parameter::path()
                ->name('token')
                ->schema(Schema::string())
                ->description('The token requested with the access code')
                ->required(),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->noSecurity()
            ->parameters(...$parameters)
            ->summary('Get the service user for the token')
            ->description('**Permission:** `Open`')
            ->operationId('service-users.token.show')
            ->tags(Tags::serviceUsers());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function appointments(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::json()->schema(AppointmentResource::list())
                ),
        ];
        $parameters = [
            Parameter::path()
                ->name('service_user')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('The service user ID')
                ->required(),
            Parameter::query()
                ->name('service_user_token')
                ->schema(Schema::string())
                ->description('The short lived service user token')
                ->required(),
            Parameter::query()
                ->name('filter[id]')
                ->schema(Schema::string())
                ->description('Comma separated appointment IDs'),
            Parameter::query()
                ->name('filter[user_id]')
                ->schema(Schema::string())
                ->description('Comma separated user IDs'),
            Parameter::query()
                ->name('filter[clinic_id]')
                ->schema(Schema::string())
                ->description('Comma separated clinic IDs'),
            Parameter::query()
                ->name('filter[available]')
                ->schema(Schema::boolean())
                ->description('If only available appointments should be returned. If the user is not authenticated, then they can only see appointments which are available'),
            Parameter::query()
                ->name('append')
                ->schema(Schema::string())
                ->description('Comma separated fields to append [`user_first_name`, `user_last_name`, `user_email`, `user_phone`]'),
            Parameter::query()
                ->name('sort')
                ->schema(Schema::string()->default('start_at'))
                ->description('The field to sort the results by [`start_at`]'),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->noSecurity()
            ->parameters(...$parameters)
            ->summary('List all appointments for the service user')
            ->description(
                <<<'EOT'
                **Permission:** `Service User`
                * View all their appointments
                EOT
            )
            ->operationId('service-users.appointments.index')
            ->tags(Tags::appointments(), Tags::serviceUsers());
    }
}

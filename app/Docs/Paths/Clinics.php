<?php

declare(strict_types=1);

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\BaseResource;
use App\Docs\Resources\ClinicResource;
use App\Docs\Responses;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class Clinics
{
    /**
     * Clinic constructor.
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
                MediaType::json(ClinicResource::list())
            ),
        ];
        $parameters = [
            Parameter::query('filter[id]', Schema::string())
                ->description('Comma separated clinic IDs'),
            Parameter::query('filter[name]', Schema::string())
                ->description('Filter the clinics by their name'),
            Parameter::query('sort', Schema::string()->default('name'))
                ->description('The field to sort the results by [`name`]'),
        ];

        return Operation::get(...$responses)
            ->security([])
            ->parameters(...$parameters)
            ->summary('List all clinics')
            ->description('**Permission:** `Open`')
            ->operationId('clinics.index')
            ->tags(Tags::clinics()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function store(): Operation
    {
        $responses = [
            Responses::http201(
                MediaType::json(ClinicResource::show())
            ),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required(
                    'name',
                    'phone',
                    'email',
                    'address_line_1',
                    'address_line_2',
                    'address_line_3',
                    'city',
                    'postcode',
                    'directions',
                    'send_cancellation_confirmations',
                    'send_dna_follow_ups'
                )
                ->properties(
                    Schema::string('name'),
                    Schema::string('phone'),
                    Schema::string('email'),
                    Schema::string('address_line_1'),
                    Schema::string('address_line_2')->nullable(),
                    Schema::string('address_line_3')->nullable(),
                    Schema::string('city'),
                    Schema::string('postcode'),
                    Schema::string('directions'),
                    Schema::integer('appointment_duration'),
                    Schema::integer('appointment_booking_threshold'),
                    Schema::boolean('send_cancellation_confirmations'),
                    Schema::boolean('send_dna_follow_ups')
                )
        );

        return Operation::post(...$responses)
            ->requestBody($requestBody)
            ->summary('Create a new clinic')
            ->description('**Permission:** `Organisation Admin`')
            ->operationId('clinics.store')
            ->tags(Tags::clinics()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function show(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(ClinicResource::show())
            ),
        ];
        $parameters = [
            Parameter::path('clinic', Schema::string()->format(Schema::UUID))
                ->description('The clinic ID')
                ->required(),
        ];

        return Operation::get(...$responses)
            ->security([])
            ->parameters(...$parameters)
            ->summary('Get a specific clinic')
            ->description('**Permission:** `Open`')
            ->operationId('clinics.show')
            ->tags(Tags::clinics()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function update(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(ClinicResource::show())
            ),
        ];
        $parameters = [
            Parameter::path('clinic', Schema::string()->format(Schema::UUID))
                ->description('The clinic ID')
                ->required(),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required(
                    'name',
                    'phone',
                    'email',
                    'address_line_1',
                    'address_line_2',
                    'address_line_3',
                    'city',
                    'postcode',
                    'directions',
                    'send_cancellation_confirmations',
                    'send_dna_follow_ups'
                )
                ->properties(
                    Schema::string('name'),
                    Schema::string('phone'),
                    Schema::string('email'),
                    Schema::string('address_line_1'),
                    Schema::string('address_line_2')->nullable(),
                    Schema::string('address_line_3')->nullable(),
                    Schema::string('city'),
                    Schema::string('postcode'),
                    Schema::string('directions'),
                    Schema::integer('appointment_duration'),
                    Schema::integer('appointment_booking_threshold'),
                    Schema::boolean('send_cancellation_confirmations'),
                    Schema::boolean('send_dna_follow_ups')
                )
        );

        $description = <<<'EOT'
**Permission:** `Clinic Admin`
* Update a clinic they are a `Clinic Admin` for
EOT;

        return Operation::put(...$responses)
            ->parameters(...$parameters)
            ->requestBody($requestBody)
            ->summary('Updated a specific clinic')
            ->description($description)
            ->operationId('clinics.update')
            ->tags(Tags::clinics()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function destroy(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(BaseResource::deleted())
            ),
        ];
        $parameters = [
            Parameter::path('clinic', Schema::string()->format(Schema::UUID))
                ->description('The clinic ID')
                ->required(),
        ];

        $description = <<<'EOT'
**Permission:** `Organisation Admin`

***

This will:
* Cancel all booked appointments in the future
* Delete all appointment schedules
* Delete all unbooked appointments
* Soft delete the clinic
EOT;

        return Operation::delete(...$responses)
            ->parameters(...$parameters)
            ->summary('Delete a specific clinic')
            ->description($description)
            ->operationId('clinics.destroy')
            ->tags(Tags::clinics()->name);
    }
}

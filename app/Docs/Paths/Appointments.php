<?php

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\AppointmentResource;
use App\Docs\Resources\BaseResource;
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
            ),
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

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function store(): Operation
    {
        $responses = [
            Responses::http201(
                MediaType::json(AppointmentResource::show())
            ),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required('clinic_id', 'start_at', 'is_repeating')
                ->properties(
                    Schema::string('clinic_id')->format(Schema::UUID),
                    Schema::string('start_at')->format(Schema::DATE_TIME),
                    Schema::boolean('is_repeating')
                )
        );

        return Operation::post(...$responses)
            ->requestBody($requestBody)
            ->summary('Create a new appointment')
            ->description(<<<EOT
**Permission:** `Community Worker`
- Create an appointment at a clinic that they are a `Community Worker` for
EOT
            )
            ->operationId('appointments.store')
            ->tags(Tags::appointments()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function show(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(AppointmentResource::show())
            ),
        ];
        $parameters = [
            Parameter::path('appointment', Schema::string()->format(Schema::UUID))
                ->description('The appointment ID')
                ->required(),
        ];

        return Operation::get(...$responses)
            ->parameters(...$parameters)
            ->summary('Get a specific appointment')
            ->description('**Permission:** `Community Worker`')
            ->operationId('appointments.show')
            ->tags(Tags::appointments()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function update(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(AppointmentResource::show())
            ),
        ];
        $parameters = [
            Parameter::path('appointment', Schema::string()->format(Schema::UUID))
                ->description('The appointment ID')
                ->required(),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required('did_not_attend')
                ->properties(
                    Schema::boolean('did_not_attend')
                )
        );

        return Operation::put(...$responses)
            ->parameters(...$parameters)
            ->requestBody($requestBody)
            ->summary('Update a specific appointment')
            ->description(<<<EOT
**Permission:** `Community Worker`
- Update their own appointment
EOT
            )
            ->operationId('appointments.update')
            ->tags(Tags::appointments()->name);
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
            Parameter::path('appointment', Schema::string()->format(Schema::UUID))
                ->description('The appointment ID')
                ->required(),
        ];

        return Operation::delete(...$responses)
            ->parameters(...$parameters)
            ->summary('Delete a specific appointment')
            ->description(<<<EOT
**Permission:** `Community Worker`
- Can delete any appointment from any user at a clinic they are a `Community Worker` for

***

An appointment can only be deleted if it has not been booked by a service user. If an appointment has been booked,
it must first be cancelled before you can delete it.
EOT
            )
            ->operationId('appointments.destroy')
            ->tags(Tags::appointments()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function cancel(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(AppointmentResource::show())
            ),
        ];
        $parameters = [
            Parameter::path('appointment', Schema::string()->format(Schema::UUID))
                ->description('The appointment ID')
                ->required(),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required('service_user_token')
                ->properties(
                    Schema::string('service_user_token')
                )
        );

        return Operation::put(...$responses)
            ->parameters(...$parameters)
            ->requestBody($requestBody)
            ->summary('Cancel a specific appointment')
            ->description(<<<EOT
**Permission:** `Open`
- Service user can cancel their own appointments

**Permission:** `Community Worker`
- Can cancel any appointment from any user at a clinic they are a `Community Worker` for

***

Removes the booking against the specified appointment.

If the service user is cancelling their own appointment then the `service_user_token` parameter must be provided.
EOT
            )
            ->operationId('appointments.cancel')
            ->tags(Tags::appointments()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function destroySchedule(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(BaseResource::deleted())
            ),
        ];
        $parameters = [
            Parameter::path('appointment', Schema::string()->format(Schema::UUID))
                ->description('The appointment ID')
                ->required(),
        ];

        return Operation::delete(...$responses)
            ->parameters(...$parameters)
            ->summary('Delete a specific repeating appointment')
            ->description(<<<EOT
**Permission:** `Community Worker`
- Can delete any appointment schedule for any user at a clinic they are a `Community Worker` for

***

Deleting the appointment schedule will attempt to delete all future appointments from the appointment specified.
If any of the future appointments have been booked, they will be skipped and not deleted. If you want them to be
deleted, you must manually cancel them and delete them individually after.
EOT
            )
            ->operationId('appointments.schedule.destroy')
            ->tags(Tags::appointments()->name);
    }
}

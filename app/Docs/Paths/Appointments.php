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
        $description = <<<EOT
**Permission:** `Open`
* View all available appointments

**Permission:** `Community Worker`
* View all appointments
EOT;

        $responses = [
            Responses::http200(
                MediaType::json(AppointmentResource::list()),
                MediaType::create(MediaType::TEXT_CALENDAR, Schema::string())
            ),
        ];
        $parameters = [
            Parameter::query('filter[id]', Schema::string())
                ->description('Comma separated appointment IDs'),
            Parameter::query('filter[user_id]', Schema::string())
                ->description('Comma separated user IDs'),
            Parameter::query('filter[clinic_id]', Schema::string())
                ->description('Comma separated clinic IDs'),
            Parameter::query('filter[service_user_id]', Schema::string())
                ->description('Comma separated service user IDs'),
            Parameter::query('filter[available]', Schema::boolean())
                ->description('If only available appointments should be returned. If the user is not authenticated, then they can only see appointments which are available'),
            Parameter::query('format', Schema::string()->enum('json', 'ics')->default('json'))
                ->description('The desired format for the response'),
            Parameter::query('calendar_feed_token', Schema::string())
                ->description('The user\'s calendar feed token - required if the format is set to `ics`'),
            Parameter::query('sort', Schema::string()->default('-created_at'))
                ->description('The field to sort the results by [`created_at`]'),
        ];

        return Operation::get(...$responses)
            ->security([])
            ->parameters(...$parameters)
            ->summary('List all appointments')
            ->description($description)
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

        $description = <<<EOT
**Permission:** `Community Worker`
- Create an appointment at a clinic that they are a `Community Worker` for
EOT;

        return Operation::post(...$responses)
            ->requestBody($requestBody)
            ->summary('Create a new appointment')
            ->description($description)
            ->operationId('appointments.store')
            ->tags(Tags::appointments()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function show(): Operation
    {
        $description = <<<EOT
**Permission:** `Open`
* View appointment if available

**Permission:** `Community Worker`
* View all appointments
EOT;
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
            ->security([])
            ->parameters(...$parameters)
            ->summary('Get a specific appointment')
            ->description($description)
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

        $description = <<<EOT
**Permission:** `Community Worker`
- Can update any appointment from any user at a clinic they are a `Community Worker` for
EOT;

        return Operation::put(...$responses)
            ->parameters(...$parameters)
            ->requestBody($requestBody)
            ->summary('Update a specific appointment')
            ->description($description)
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

        $description = <<<EOT
**Permission:** `Community Worker`
- Can delete any appointment from any user at a clinic they are a `Community Worker` for

***

An appointment can only be deleted if it has not been booked by a service user. If an appointment has been booked,
it must first be cancelled before you can delete it.
EOT;

        return Operation::delete(...$responses)
            ->parameters(...$parameters)
            ->summary('Delete a specific appointment')
            ->description($description)
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

        $description = <<<EOT
**Permission:** `Open`
- Service user can cancel their own appointments

**Permission:** `Community Worker`
- Can cancel any appointment from any user at a clinic they are a `Community Worker` for

***

Removes the booking against the specified appointment.

If the service user is cancelling their own appointment then the `service_user_token` parameter must be provided.
EOT;

        return Operation::put(...$responses)
            ->security([])
            ->parameters(...$parameters)
            ->requestBody($requestBody)
            ->summary('Cancel a specific appointment')
            ->description($description)
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

        $description = <<<EOT
**Permission:** `Community Worker`
- Can delete any appointment schedule for any user at a clinic they are a `Community Worker` for

***

Deleting the appointment schedule will attempt to delete all future appointments from the appointment specified.
If any of the future appointments have been booked, they will be skipped and not deleted. If you want them to be
deleted, you must manually cancel them and delete them individually after.
EOT;

        return Operation::delete(...$responses)
            ->parameters(...$parameters)
            ->summary('Delete a specific repeating appointment')
            ->description($description)
            ->operationId('appointments.schedule.destroy')
            ->tags(Tags::appointments()->name);
    }
}

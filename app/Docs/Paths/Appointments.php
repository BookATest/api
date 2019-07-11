<?php

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\AppointmentResource;
use App\Docs\Resources\BaseResource;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
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
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function index(): Operation
    {
        $appointmentsVisible = config('bat.days_in_advance_to_book');

        $responses = [
            Response::ok()->content(
                MediaType::json()->schema(AppointmentResource::list())
            ),
        ];
        $parameters = [
            Parameter::query()
                ->name('append')
                ->schema(Schema::string())
                ->description('Comma separated fields to append [`service_user_name`, `user_first_name`, `user_last_name`, `user_email`, `user_phone`]'),
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
                ->name('filter[service_user_id]')
                ->schema(Schema::string())
                ->description('Comma separated service user IDs'),
            Parameter::query()
                ->name('filter[available]')
                ->schema(Schema::boolean())
                ->description('If only available appointments should be returned. If the user is not authenticated, then they can only see appointments which are available'),
            Parameter::query()
                ->name('filter[starts_after]')
                ->schema(Schema::string()->format(Schema::FORMAT_DATE_TIME))
                ->description('The date and time to get appointments starting after'),
            Parameter::query()
                ->name('filter[starts_before]')
                ->schema(Schema::string()->format(Schema::FORMAT_DATE_TIME))
                ->description('The date and time to get appointments starting before'),
            Parameter::query()
                ->name('sort')
                ->schema(Schema::string()->default('start_at'))
                ->description('The field to sort the results by [`start_at`]'),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->noSecurity()
            ->parameters(...$parameters)
            ->summary('List all appointments')
            ->description(
                <<<EOT
                **Permission:** `Open`
                * View all available appointments within the next {$appointmentsVisible} days
                * Cannot append `service_user_name`
                
                **Permission:** `Community Worker`
                * View all appointments
                EOT
            )
            ->operationId('appointments.index')
            ->tags(Tags::appointments());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function store(): Operation
    {
        $responses = [
            Response::created()
                ->content(
                    MediaType::json()->schema(AppointmentResource::show())
                ),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required('clinic_id', 'start_at', 'is_repeating')
                ->properties(
                    Schema::string('clinic_id')->format(Schema::FORMAT_UUID),
                    Schema::string('start_at')->format(Schema::FORMAT_DATE_TIME),
                    Schema::boolean('is_repeating')
                )
        );

        return Operation::post()
            ->responses(...$responses)
            ->requestBody($requestBody)
            ->summary('Create a new appointment')
            ->description(
                <<<'EOT'
                **Permission:** `Community Worker`
                - Create an appointment at a clinic that they are a `Community Worker` for
                EOT
            )
            ->operationId('appointments.store')
            ->tags(Tags::appointments());
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
                    MediaType::json()->schema(AppointmentResource::show())
                ),
        ];
        $parameters = [
            Parameter::path()
                ->name('appointment')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('The appointment ID')
                ->required(),
            Parameter::query()
                ->name('append')
                ->schema(Schema::string())
                ->description('Comma separated fields to append [`service_user_name`, `user_first_name`, `user_last_name`, `user_email`, `user_phone`]'),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->noSecurity()
            ->parameters(...$parameters)
            ->summary('Get a specific appointment')
            ->description(
                <<<'EOT'
                **Permission:** `Open`
                * View appointment if available
                
                **Permission:** `Community Worker`
                * View all appointments
                EOT
            )
            ->operationId('appointments.show')
            ->tags(Tags::appointments());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function update(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::json()->schema(AppointmentResource::show())
                ),
        ];
        $parameters = [
            Parameter::path()
                ->name('appointment')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
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

        return Operation::put()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->requestBody($requestBody)
            ->summary('Update a specific appointment')
            ->description(
                <<<'EOT'
                **Permission:** `Community Worker`
                - Can update any appointment from any user at a clinic they are a `Community Worker` for
                EOT
            )
            ->operationId('appointments.update')
            ->tags(Tags::appointments());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function destroy(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::json()->schema(BaseResource::deleted())
                ),
        ];
        $parameters = [
            Parameter::path()
                ->name('appointment')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('The appointment ID')
                ->required(),
        ];

        return Operation::delete()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('Delete a specific appointment')
            ->description(
                <<<'EOT'
                **Permission:** `Community Worker`
                - Can delete any appointment from any user at a clinic they are a `Community Worker` for
                
                ***
                
                An appointment can only be deleted if it has not been booked by a service user. If an appointment has been booked,
                it must first be cancelled before you can delete it.
                EOT
            )
            ->operationId('appointments.destroy')
            ->tags(Tags::appointments());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function cancel(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::json()->schema(AppointmentResource::show())
                ),
        ];
        $parameters = [
            Parameter::path()
                ->name('appointment')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
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

        return Operation::put()
            ->responses(...$responses)
            ->noSecurity()
            ->parameters(...$parameters)
            ->requestBody($requestBody)
            ->summary('Cancel a specific appointment')
            ->description(
                <<<'EOT'
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
            ->tags(Tags::appointments());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function destroySchedule(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::json()->schema(BaseResource::deleted())
                ),
        ];
        $parameters = [
            Parameter::path()
                ->name('appointment')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('The appointment ID')
                ->required(),
        ];

        return Operation::delete()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('Delete a specific repeating appointment')
            ->description(
                <<<'EOT'
                **Permission:** `Community Worker`
                - Can delete any appointment schedule for any user at a clinic they are a `Community Worker` for
                
                ***
                
                Deleting the appointment schedule will attempt to delete all future appointments from the appointment specified.
                If any of the future appointments have been booked, they will be skipped and not deleted. If you want them to be
                deleted, you must manually cancel them and delete them individually after.
                EOT
            )
            ->operationId('appointments.schedule.destroy')
            ->tags(Tags::appointments());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function indexIcs(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::calendar()->schema(Schema::string())
                ),
        ];
        $parameters = [
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
                ->name('filter[service_user_id]')
                ->schema(Schema::string())
                ->description('Comma separated service user IDs'),
            Parameter::query()
                ->name('filter[available]')
                ->schema(Schema::boolean())
                ->description('If only available appointments should be returned. If the user is not authenticated, then they can only see appointments which are available'),
            Parameter::query()
                ->name('filter[starts_after]')
                ->schema(Schema::string()->format(Schema::FORMAT_DATE_TIME))
                ->description('The date and time to get appointments starting after'),
            Parameter::query()
                ->name('filter[starts_before]')
                ->schema(Schema::string()->format(Schema::FORMAT_DATE_TIME))
                ->description('The date and time to get appointments starting before'),
            Parameter::query()
                ->name('calendar_feed_token')
                ->schema(Schema::string())
                ->description('The user\'s calendar feed token - required if the format is set to `ics`')
                ->required(),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->noSecurity()
            ->parameters(...$parameters)
            ->summary('Stream all appointments as an ICS feed')
            ->description(
                <<<'EOT'
                **Permission:** `Open`
                * View all appointments
                EOT
            )
            ->operationId('appointments.index.ics')
            ->tags(Tags::appointments());
    }
}

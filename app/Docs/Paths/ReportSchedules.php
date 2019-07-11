<?php

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\BaseResource;
use App\Docs\Resources\ReportScheduleResource;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class ReportSchedules
{
    /**
     * ReportSchedules constructor.
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
                    MediaType::json()->schema(ReportScheduleResource::list())
                ),
        ];

        $parameters = [
            Parameter::query()
                ->name('filter[id]')
                ->schema(Schema::string())
                ->description('Comma separated report schedule IDs'),
            Parameter::query()
                ->name('filter[clinic_id]')
                ->schema(Schema::string())
                ->description('Comma separated clinic IDs'),
            Parameter::query()
                ->name('sort')
                ->schema(Schema::string()->default('-created_at'))
                ->description('The field to sort the results by [`created_at`]'),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('List all report schedules')
            ->description(
                <<<'EOT'
                **Permission:** `Community Worker`
                - List their own report schedules
                EOT
            )
            ->operationId('report-schedules.index')
            ->tags(Tags::reportSchedules());
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
                    MediaType::json()->schema(ReportScheduleResource::show())
                ),
        ];

        $requestBody = Requests::json(
            Schema::object()
                ->required('user_id', 'report_type', 'repeat_type')
                ->properties(
                    Schema::string('clinic_id')->format(Schema::FORMAT_UUID),
                    Schema::string('report_type')->enum('general_export'),
                    Schema::string('repeat_type')->enum('weekly', 'monthly')
                )
        );

        return Operation::post()
            ->responses(...$responses)
            ->requestBody($requestBody)
            ->summary('Create a new report schedule')
            ->description(
                <<<'EOT'
                **Permission:** `Community Worker`
                - Create reports for them self
                EOT
            )
            ->operationId('report-schedules.store')
            ->tags(Tags::reportSchedules());
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
                    MediaType::json()->schema(ReportScheduleResource::show())
                ),
        ];

        $parameters = [
            Parameter::path()
                ->name('report_schedule')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('The report schedule ID')
                ->required(),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('Show the specified report schedule')
            ->description(
                <<<'EOT'
                **Permission:** `Community Worker`
                - View their own report schedule
                EOT
            )
            ->operationId('report-schedules.show')
            ->tags(Tags::reportSchedules());
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
                ->name('report_schedule')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('The report schedule ID')
                ->required(),
        ];

        return Operation::delete()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('Delete the specified report schedule')
            ->description(
                <<<'EOT'
                **Permission:** `Community Worker`
                - Delete their own report schedule
                EOT
            )
            ->operationId('report-schedules.destroy')
            ->tags(Tags::reportSchedules());
    }
}

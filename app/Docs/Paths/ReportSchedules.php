<?php

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\BaseResource;
use App\Docs\Resources\ReportScheduleResource;
use App\Docs\Responses;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
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
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function index(): Operation
    {
        $description = <<<EOT
**Permission:** `Community Worker`
- List their own report schedules
EOT;

        $responses = [
            Responses::http200(
                MediaType::json(ReportScheduleResource::list())
            ),
        ];

        $parameters = [
            Parameter::query('user_id', Schema::string()->format(Schema::UUID))
                ->description('Comma separated user IDs'),
        ];

        return Operation::get(...$responses)
            ->parameters(...$parameters)
            ->summary('List all report schedules')
            ->description($description)
            ->operationId('report-schedules.index')
            ->tags(Tags::reportSchedules()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function store(): Operation
    {
        $description = <<<EOT
**Permission:** `Community Worker`
- Create reports for them self
EOT;

        $responses = [
            Responses::http201(
                MediaType::json(ReportScheduleResource::show())
            ),
        ];

        $requestBody = Requests::json(Schema::object()
            ->required('user_id', 'report_type', 'repeat_type')
            ->properties(
                Schema::string('user_id')->format(Schema::UUID),
                Schema::string('clinic_id')->format(Schema::UUID),
                Schema::string('report_type')->enum('Report Type 1', 'Report Type 2'),
                Schema::string('repeat_type')->enum('weekly', 'monthly')
            )
        );

        return Operation::post(...$responses)
            ->requestBody($requestBody)
            ->summary('Create a new report schedule')
            ->description($description)
            ->operationId('report-schedules.store')
            ->tags(Tags::reportSchedules()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function show(): Operation
    {
        $description = <<<EOT
**Permission:** `Community Worker`
- View their own report schedule
EOT;

        $responses = [
            Responses::http200(
                MediaType::json(ReportScheduleResource::show())
            ),
        ];

        $parameters = [
            Parameter::path('report_schedule', Schema::string()->format(Schema::UUID))
                ->description('The report schedule ID')
                ->required(),
        ];

        return Operation::get(...$responses)
            ->parameters(...$parameters)
            ->summary('Show the specified report schedule')
            ->description($description)
            ->operationId('report-schedules.show')
            ->tags(Tags::reportSchedules()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function update(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(ReportScheduleResource::show())
            ),
        ];
        $parameters = [
            Parameter::path('report_schedule', Schema::string()->format(Schema::UUID))
                ->description('The report schedule ID')
                ->required(),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required('report_type', 'repeat_type')
                ->properties(
                    Schema::string('clinic_id')->format(Schema::UUID),
                    Schema::string('report_type')->enum('Report Type 1', 'Report Type 2'),
                    Schema::string('repeat_type')->enum('weekly', 'monthly')
                )
        );

        $description = <<<EOT
**Permission:** `Community Worker`
- Delete their own report schedule
EOT;

        return Operation::put(...$responses)
            ->parameters(...$parameters)
            ->requestBody($requestBody)
            ->summary('Update a specific report schedule')
            ->description($description)
            ->operationId('report-schedules.update')
            ->tags(Tags::reportSchedules()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function destroy(): Operation
    {
        $description = <<<EOT
**Permission:** `Community Worker`
- Delete their own report schedule
EOT;

        $responses = [
            Responses::http200(
                MediaType::json(BaseResource::deleted())
            ),
        ];

        $parameters = [
            Parameter::path('report_schedule', Schema::string()->format(Schema::UUID))
                ->description('The report schedule ID')
                ->required(),
        ];

        return Operation::delete(...$responses)
            ->parameters(...$parameters)
            ->summary('Delete the specified report schedule')
            ->description($description)
            ->operationId('report-schedules.destroy')
            ->tags(Tags::reportSchedules()->name);
    }
}
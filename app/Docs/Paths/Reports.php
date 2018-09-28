<?php

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\BaseResource;
use App\Docs\Resources\ReportResource;
use App\Docs\Responses;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class Reports
{
    /**
     * Reports constructor.
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
- List their own reports
EOT;

        $responses = [
            Responses::http200(
                MediaType::json(ReportResource::list())
            ),
        ];

        $parameters = [
            Parameter::query('user_id', Schema::string()->format(Schema::UUID))
                ->description('Comma separated user IDs'),
        ];

        return Operation::get(...$responses)
            ->parameters(...$parameters)
            ->summary('List all reports')
            ->description($description)
            ->operationId('reports.index')
            ->tags(Tags::reports()->name);
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
                MediaType::json(ReportResource::show())
            ),
        ];

        $requestBody = Requests::json(Schema::object()
            ->required('user_id', 'type', 'start_at', 'end_at')
            ->properties(
                Schema::string('user_id')->format(Schema::UUID),
                Schema::string('clinic_id')->format(Schema::UUID),
                Schema::string('type'),
                Schema::string('start_at')->format(Schema::DATE),
                Schema::string('end_at')->format(Schema::DATE)
            )
        );

        return Operation::post(...$responses)
            ->requestBody($requestBody)
            ->summary('Create a new report')
            ->description($description)
            ->operationId('reports.store')
            ->tags(Tags::reports()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function show(): Operation
    {
        $description = <<<EOT
**Permission:** `Community Worker`
- Show their own reports
EOT;

        $responses = [
            Responses::http200(
                MediaType::json(ReportResource::show())
            ),
        ];

        $parameters = [
            Parameter::path('report', Schema::string()->format(Schema::UUID))
                ->description('The report ID')
                ->required(),
        ];

        return Operation::get(...$responses)
            ->parameters(...$parameters)
            ->summary('Show the specified report')
            ->description($description)
            ->operationId('reports.show')
            ->tags(Tags::reports()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function destroy(): Operation
    {
        $description = <<<EOT
**Permission:** `Community Worker`
- Delete their own reports
EOT;

        $responses = [
            Responses::http200(
                MediaType::json(BaseResource::deleted())
            ),
        ];

        $parameters = [
            Parameter::path('report', Schema::string()->format(Schema::UUID))
                ->description('The report ID')
                ->required(),
        ];

        return Operation::delete(...$responses)
            ->parameters(...$parameters)
            ->summary('Delete the specified report')
            ->description($description)
            ->operationId('reports.destroy')
            ->tags(Tags::reports()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function download(): Operation
    {
        $description = <<<EOT
**Permission:** `Community Worker`
- Download their own reports
EOT;

        $responses = [
            Responses::http200(
                MediaType::create('application/pdf', Schema::string()->format(Schema::BINARY))
            ),
        ];

        $parameters = [
            Parameter::path('report', Schema::string()->format(Schema::UUID))
                ->description('The report ID')
                ->required(),
        ];

        return Operation::get(...$responses)
            ->parameters(...$parameters)
            ->summary('Download the specified report')
            ->description($description)
            ->operationId('reports.download')
            ->tags(Tags::reports()->name);
    }
}

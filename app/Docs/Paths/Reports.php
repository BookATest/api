<?php

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\BaseResource;
use App\Docs\Resources\ReportResource;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
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
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function index(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::json()->schema(ReportResource::list())
                ),
        ];

        $parameters = [
            Parameter::query()
                ->name('filter[id]')
                ->schema(Schema::string())
                ->description('Comma separated report IDs'),
            Parameter::query()
                ->name('filter[clinic_id]')
                ->schema(Schema::string())
                ->description('Comma separated clinic IDs'),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('List all reports')
            ->description(
                <<<'EOT'
                **Permission:** `Community Worker`
                - List their own reports
                EOT
            )
            ->operationId('reports.index')
            ->tags(Tags::reports());
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
                    MediaType::json()->schema(ReportResource::show())
                ),
        ];

        $requestBody = Requests::json(
            Schema::object()
                ->required('type', 'start_at', 'end_at')
                ->properties(
                    Schema::string('clinic_id')->format(Schema::FORMAT_UUID),
                    Schema::string('type')->enum('general_export'),
                    Schema::string('start_at')->format(Schema::FORMAT_DATE),
                    Schema::string('end_at')->format(Schema::FORMAT_DATE)
                )
        );

        return Operation::post()
            ->responses(...$responses)
            ->requestBody($requestBody)
            ->summary('Create a new report')
            ->description(
                <<<'EOT'
                **Permission:** `Community Worker`
                - Create reports for them self
                EOT
            )
            ->operationId('reports.store')
            ->tags(Tags::reports());
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
                    MediaType::json()->schema(ReportResource::show())
                ),
        ];

        $parameters = [
            Parameter::path()
                ->name('report')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('The report ID')
                ->required(),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('Show the specified report')
            ->description(
                <<<'EOT'
                **Permission:** `Community Worker`
                - Show their own reports
                EOT
            )
            ->operationId('reports.show')
            ->tags(Tags::reports());
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
                ->name('report')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('The report ID')
                ->required(),
        ];

        return Operation::delete()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('Delete the specified report')
            ->description(
                <<<'EOT'
                **Permission:** `Community Worker`
                - Delete their own reports
                EOT
            )
            ->operationId('reports.destroy')
            ->tags(Tags::reports());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function download(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::pdf()->schema(
                        Schema::string()->format(Schema::FORMAT_BINARY)
                    )
                ),
        ];

        $parameters = [
            Parameter::path()
                ->name('report')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('The report ID')
                ->required(),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('Download the specified report')
            ->description(
                <<<'EOT'
                **Permission:** `Community Worker`
                - Download their own reports
                EOT
            )
            ->operationId('reports.download')
            ->tags(Tags::reports());
    }
}

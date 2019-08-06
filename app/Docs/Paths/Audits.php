<?php

namespace App\Docs\Paths;

use App\Docs\Resources\AuditResource;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class Audits
{
    /**
     * Audits constructor.
     */
    public function __construct()
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
                    MediaType::json()->schema(AuditResource::list())
                ),
        ];
        $parameters = [
            Parameter::query()
                ->name('filter[id]')
                ->schema(Schema::string())
                ->description('Comma separated audit IDs'),
            Parameter::query()
                ->name('sort')
                ->schema(Schema::string()->default('-created_at'))
                ->description('The field to sort the results by [`created_at`]'),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('List all audits')
            ->description('**Permission:** `Organisation Admin`')
            ->operationId('audits.index')
            ->tags(Tags::audits());
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
                    MediaType::json()->schema(AuditResource::show())
                ),
        ];
        $parameters = [
            Parameter::path()
                ->name('audit')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('The audit ID')
                ->required(),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('Get a specific audit')
            ->description('**Permission:** `Organisation Admin`')
            ->operationId('audits.show')
            ->tags(Tags::audits());
    }
}

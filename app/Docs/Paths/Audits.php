<?php

namespace App\Docs\Paths;

use App\Docs\Resources\AuditResource;
use App\Docs\Responses;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
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
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function index(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(AuditResource::list())
            ),
        ];
        $parameters = [
            Parameter::query('filter[id]', Schema::string())
                ->description('Comma separated audit IDs'),
            Parameter::query('sort', Schema::string()->default('-created_at'))
                ->description('The field to sort the results by [`created_at`]'),
        ];

        return Operation::get(...$responses)
            ->parameters(...$parameters)
            ->summary('List all audits')
            ->description('**Permission:** `Organisation Admin`')
            ->operationId('audits.index')
            ->tags(Tags::audits()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function show(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(AuditResource::show())
            ),
        ];
        $parameters = [
            Parameter::path('audit', Schema::string()->format(Schema::UUID))
                ->description('The audit ID')
                ->required(),
        ];

        return Operation::get(...$responses)
            ->parameters(...$parameters)
            ->summary('Get a specific audit')
            ->description('**Permission:** `Organisation Admin`')
            ->operationId('audits.show')
            ->tags(Tags::audits()->name);
    }
}

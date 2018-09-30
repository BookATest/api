<?php

namespace App\Docs\Paths;

use App\Docs\Resources\AuditResource;
use App\Docs\Responses;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;

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

        return Operation::get(...$responses)
            ->summary('List all audits')
            ->description('**Permission:** `Organisation Admin`')
            ->operationId('audits.index')
            ->tags(Tags::audits()->name);
    }
}

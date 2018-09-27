<?php

namespace App\Docs\Paths;

use App\Docs\Resources\ClinicResource;
use App\Docs\Responses;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;

class Clinics
{
    /**
     * Clinic constructor.
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
                MediaType::json(ClinicResource::list())
            ),
        ];

        return Operation::get(...$responses)
            ->summary('List all clinics')
            ->description('**Permission:** `Open`')
            ->operationId('clinics.index')
            ->tags(Tags::clinics()->name);
    }
}

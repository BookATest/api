<?php

namespace App\Docs\Paths;

use App\Docs\Resources\StatResource;
use App\Docs\Responses;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class Stats
{
    /**
     * Stats constructor.
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

***

The stats returned will vary depending on the calling user's role (i.e. more stats will be available for users
with greater roles).
EOT;


        $responses = [
            Responses::http200(
                MediaType::json(StatResource::show())
            ),
        ];
        $parameters = [
            Parameter::query('filter[clinic_id]', Schema::string())
                ->description('Comma separated clinic IDs'),
        ];

        return Operation::get(...$responses)
            ->parameters(...$parameters)
            ->summary('The dashboard stats for the user')
            ->description($description)
            ->operationId('stats.index')
            ->tags(Tags::stats()->name);
    }
}

<?php

namespace App\Docs\Paths;

use App\Docs\Resources\StatResource;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
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
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function index(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::json()->schema(StatResource::show())
                ),
        ];
        $parameters = [
            Parameter::query()
                ->name('filter[clinic_id]')
                ->schema(Schema::string())
                ->description('Comma separated clinic IDs'),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('The dashboard stats for the user')
            ->description(
                <<<'EOT'
                **Permission:** `Community Worker`
                
                ***
                
                The stats returned will vary depending on the calling user's role (i.e. more stats will be available for users
                with greater roles).
                EOT
            )
            ->operationId('stats.index')
            ->tags(Tags::stats());
    }
}

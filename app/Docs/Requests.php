<?php

namespace App\Docs;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class Requests
{
    /**
     * Requests constructor.
     */
    protected function __construct()
    {
        // Prevent instantiation.
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema $schema
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody
     */
    public static function json(Schema $schema): RequestBody
    {
        return RequestBody::create()
            ->content(
                MediaType::json()->schema($schema)
            )
            ->required();
    }
}

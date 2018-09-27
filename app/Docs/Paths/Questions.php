<?php

namespace App\Docs\Paths;

use App\Docs\Resources\QuestionResource;
use App\Docs\Responses;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;

class Questions
{
    /**
     * Questions constructor.
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
**Permission:** `Open`

***

This endpoint will only return a list of all the current eligibility questions.

It's important to realise that previous questions cannot be accessed through the API, even though they remain in
the database.
EOT;

        $responses = [
            Responses::http200(
                MediaType::json(QuestionResource::all())
            ),
        ];

        return Operation::get(...$responses)
            ->summary('List all questions')
            ->description($description)
            ->operationId('questions.index')
            ->tags(Tags::questions()->name);
    }
}

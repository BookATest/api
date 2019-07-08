<?php

declare(strict_types=1);

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\QuestionResource;
use App\Docs\Responses;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

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
        $description = <<<'EOT'
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
            ->security([])
            ->summary('List all questions')
            ->description($description)
            ->operationId('questions.index')
            ->tags(Tags::questions()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function store(): Operation
    {
        $description = <<<'EOT'
**Permission:** `Organisation Admin`

***

This will create a completely new set of eligibility questions.

Even if you only intend to add a new question, the entire set will be recreated. This means all clinics will
need to respecify their eligibility criteria.
EOT;

        $responses = [
            Responses::http200(
                MediaType::json(QuestionResource::all())
            ),
        ];

        $requestBody = Requests::json(Schema::object()
            ->required('questions')
            ->properties(
                Schema::array('questions')->items(Schema::object()
                    ->required('question', 'type', 'options')
                    ->properties(
                        Schema::string('question'),
                        Schema::string('type'),
                        Schema::array('options')->items(Schema::string())
                    ))
            ));

        return Operation::post(...$responses)
            ->requestBody($requestBody)
            ->summary('Create a new set of eligibility questions')
            ->description($description)
            ->operationId('questions.store')
            ->tags(Tags::questions()->name);
    }
}

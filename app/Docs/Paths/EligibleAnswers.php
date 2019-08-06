<?php

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\EligibleAnswerResource;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class EligibleAnswers
{
    /**
     * EligibleAnswers constructor.
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
                    MediaType::json()->schema(EligibleAnswerResource::all())
                ),
        ];
        $parameters = [
            Parameter::path()
                ->name('clinic')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('The clinic ID')
                ->required(),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('List all the eligible answers set by the clinic')
            ->description(
                <<<'EOT'
                **Permission:** `Clinic Admin`
                - Can view all eligible answers at a clinic they are a `Clinic Admin` for
                
                ***
                
                This endpoint will only return a list of all the eligibility answers for the current questions.
                
                It's important to realise that answers for previous questions cannot be accessed through the API, even though they
                remain in the database.
                EOT
            )
            ->operationId('clinics.eligible-answers.show')
            ->tags(Tags::eligibleAnswers());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function update(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::json()->schema(EligibleAnswerResource::all())
                ),
        ];
        $parameters = [
            Parameter::path()
                ->name('clinic')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('The clinic ID')
                ->required(),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required('answers')
                ->properties(
                    Schema::array('answers')
                        ->items(
                            Schema::object()
                                ->required('question_id', 'answer')
                                ->properties(
                                    Schema::string('question_id')->format(Schema::FORMAT_UUID),
                                    Schema::object('answer')
                                        ->required('comparison', 'interval')
                                        ->properties(
                                            Schema::string('comparison')->enum('>', '<'),
                                            Schema::integer('interval')
                                        )
                                )
                        )
                )
        );

        return Operation::put()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->requestBody($requestBody)
            ->summary('Update the eligibility answers')
            ->description(
                <<<'EOT'
                **Permission:** `Clinic Admin`
                - Can update the set of eligible answers for a clinic they are a `Clinic Admin` for
                EOT
            )
            ->operationId('clinics.eligible-answers.update')
            ->tags(Tags::eligibleAnswers());
    }
}

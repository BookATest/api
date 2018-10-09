<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\EligibleAnswer\{IndexRequest, UpdateRequest};
use App\Http\Resources\EligibleAnswerResource;
use App\Models\Clinic;
use App\Models\EligibleAnswer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EligibleAnswerController extends Controller
{
    /**
     * EligibleAnswerController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * @param \App\Http\Requests\EligibleAnswer\IndexRequest $request
     * @param \App\Models\Clinic $clinic
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexRequest $request, Clinic $clinic)
    {
        if (!$clinic->hasEligibleAnswers()) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $eligibleAnswers = $clinic->eligibleAnswers()->current()->get();

        event(EndpointHit::onRead($request, "Viewed eligible answers for clinic [$clinic->id]"));

        return EligibleAnswerResource::collection($eligibleAnswers);
    }

    /**
     * @param \App\Http\Requests\EligibleAnswer\UpdateRequest $request
     * @param \App\Models\Clinic $clinic
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function update(UpdateRequest $request, Clinic $clinic)
    {
        $eligibleAnswers = DB::transaction(function () use ($request, $clinic): Collection {
            $eligibleAnswers = new Collection();

            foreach ($request->answers as $answer) {
                $eligibleAnswer = $clinic->eligibleAnswers()->updateOrCreate(
                    ['question_id' => $answer['question_id']],
                    ['answer' => $answer['answer']]
                );

                $eligibleAnswers->push($eligibleAnswer);
            }

            return $eligibleAnswers;
        });

        event(EndpointHit::onUpdate($request, "Updated eligible answers for clinic [$clinic->id]"));

        return EligibleAnswerResource::collection($eligibleAnswers);
    }
}

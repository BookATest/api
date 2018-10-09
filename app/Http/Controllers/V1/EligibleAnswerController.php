<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\EligibleAnswer\{IndexRequest};
use App\Http\Resources\EligibleAnswerResource;
use App\Models\Clinic;
use Illuminate\Http\Response;

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
        $eligibleAnswers = $clinic->eligibleAnswers()->current()->get();

        if ($eligibleAnswers->isEmpty()) {
            abort(Response::HTTP_NOT_FOUND);
        }

        event(EndpointHit::onRead($request, "Viewed all eligible answers for clinic [$clinic->id]"));

        return EligibleAnswerResource::collection($eligibleAnswers);
    }
}

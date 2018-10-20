<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Requests\Question\IndexRequest;
use App\Http\Requests\Question\StoreRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    /**
     * QuestionController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api')->except('index');
    }

    /**
     * Display a listing of the resource.
     *
     * @param \App\Http\Requests\Question\IndexRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexRequest $request)
    {
        event(EndpointHit::onRead($request, 'Listed all questions'));

        return QuestionResource::collection(Question::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Question\StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        $questions = DB::transaction(function () use ($request) {
            // Invalidate all current questions.
            Question::invalidateAll();

            // Create the new set of questions.
            $questions = new Collection();

            foreach ($request->questions as $question) {
                switch ($question['type']) {
                    case Question::CHECKBOX:
                        $questions->push(Question::createCheckbox($question['question']));
                        break;
                    case Question::DATE:
                        $questions->push(Question::createDate($question['question']));
                        break;
                    case Question::TEXT:
                        $questions->push(Question::createText($question['question']));
                        break;
                    case Question::SELECT:
                        $questions->push(Question::createSelect($question['question'], ...$question['options']));
                        break;
                }
            }

            return $questions;
        });

        event(EndpointHit::onCreate($request, "Created questions"));

        return QuestionResource::collection($questions)
            ->toResponse($request)
            ->setStatusCode(Response::HTTP_CREATED);
    }
}

<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Requests\Question\StoreRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    /**
     * QuestionController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api')->only('store');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Question\StoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        event(EndpointHit::onCreate($request, 'Created question set'));

        return DB::transaction(function () use ($request) {
            // Soft delete all current questions.
            Question::query()->delete();

            // Initialise an empty question collection.
            $questions = new Collection();

            // Loop through each posted question.
            foreach ($request->input('questions') as $questionData) {
                // Create a question instance.
                $question = Question::create([
                    'question' => $questionData['question'],
                    'type' => $questionData['type'],
                ]);

                // If the question type was `select`, then loop through each option.
                if ($questionData['type'] == Question::SELECT) {
                    foreach ($questionData['options'] as $option) {
                        // Create a question option instance.
                        $question->questionOptions()->create(['option' => $option]);
                    }
                }

                // Append the question to the collection.
                $questions->push($question);
            }

            return QuestionResource::collection($questions);
        });
    }
}

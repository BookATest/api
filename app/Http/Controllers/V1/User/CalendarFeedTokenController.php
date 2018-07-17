<?php

namespace App\Http\Controllers\V1\User;

use App\Events\EndpointHit;
use App\Http\Requests\User\CalendarFeedToken\UpdateRequest;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CalendarFeedTokenController extends Controller
{
    /**
     * CalendarFeedTokenController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\User\CalendarFeedToken\UpdateRequest $request
     * @param \App\Models\User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, User $user)
    {
        event(EndpointHit::onUpdate($request, "Updated calendar feed token for user [$user->id]"));

        return DB::transaction(function () use ($user) {
            $user->generateCalendarFeedToken();

            return response()->json(['calendar_feed_token' => $user->calendar_feed_token]);
        });
    }
}

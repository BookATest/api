<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\User;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CalendarFeedToken\UpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CalendarFeedTokenController extends Controller
{
    /**
     * CalendarFeedTokenController constructor.
     */
    public function __construct()
    {
        $this->middleware('throttle:60,1');
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
        $calendarFeedToken = DB::transaction(function () use ($user) {
            $user->update(['calendar_feed_token' => User::generateCalendarFeedToken()]);

            return $user->calendar_feed_token;
        });

        event(EndpointHit::onUpdate($request, "Updated calendar feed token for user [$user->id]"));

        return response()->json(['calendar_feed_token' => $calendarFeedToken]);
    }
}

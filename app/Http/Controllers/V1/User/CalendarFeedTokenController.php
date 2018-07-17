<?php

namespace App\Http\Controllers\V1\User;

use App\Events\EndpointHit;
use App\Models\User;
use Illuminate\Http\Request;
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
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        abort_if($request->user()->id !== $user->id, Response::HTTP_FORBIDDEN);

        event(EndpointHit::onUpdate($request, "Updated calendar feed token for user [$user->id]"));

        return DB::transaction(function () use ($user) {
            $user->generateCalendarFeedToken();

            return response()->json(['calendar_feed_token' => $user->calendar_feed_token]);
        });
    }
}

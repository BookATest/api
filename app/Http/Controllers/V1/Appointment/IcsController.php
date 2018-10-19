<?php

namespace App\Http\Controllers\V1\Appointment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\IcsRequest;
use App\Models\User;

class IcsController extends Controller
{
    public function __invoke(IcsRequest $request)
    {
        $user = User::findByCalendarFeedToken($request->calendar_feed_token);

        // TODO: Implement __invoke() method.
    }
}

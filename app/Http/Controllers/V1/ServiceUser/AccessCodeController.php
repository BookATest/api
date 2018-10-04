<?php

namespace App\Http\Controllers\V1\ServiceUser;

use App\Contracts\SmsSender;
use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceUser\AccessCodeRequest;
use App\Models\ServiceUser;
use App\Notifications\Sms\AccessCodeSms;
use Illuminate\Foundation\Bus\DispatchesJobs;

class AccessCodeController extends Controller
{
    use DispatchesJobs;

    /**
     * @param \App\Http\Requests\ServiceUser\AccessCodeRequest $request
     * @param \App\Contracts\SmsSender $sender
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(AccessCodeRequest $request, SmsSender $sender)
    {
        $serviceUser = ServiceUser::findByPhone($request->phone);

        // Only dispatch the SMS if the phone number is valid.
        if ($serviceUser) {
            $accessCode = $serviceUser->generateAccessCode();
            $this->dispatch(new AccessCodeSms($serviceUser, $accessCode));
        }

        event(EndpointHit::onCreate($request, "Requested access code with phone [{$request->phone}]"));

        return response()->json([
            'message' => 'If the number provided has been used to make a booking, then an access code has been sent.',
        ]);
    }
}

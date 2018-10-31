<?php

namespace App\Http\Controllers\V1\Appointment;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\CancelRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

class CancelController extends Controller
{
    /**
     * @param \App\Http\Requests\Appointment\CancelRequest $request
     * @param \App\Models\Appointment $appointment
     * @return \App\Http\Resources\AppointmentResource
     */
    public function __invoke(CancelRequest $request, Appointment $appointment)
    {
        $appointment = DB::transaction(function () use ($request, $appointment) {
            $serviceUser = $appointment->serviceUser;

            $appointment->cancel();

            $serviceUserInitiated = $request->user() === null;

            if ($serviceUserInitiated) {
                $this->dispatch(new \App\Notifications\Email\User\BookingCancelledByServiceUserEmail($appointment));
                $this->dispatch(new \App\Notifications\Sms\ServiceUser\BookingCancelledByServiceUserSms($appointment));

                if ($serviceUser->email) {
                    $this->dispatch(new \App\Notifications\Email\ServiceUser\BookingCancelledByServiceUserEmail($appointment));
                }
            } else {
                //
            }

            return $appointment;
        });

        event(EndpointHit::onUpdate($request, "Cancelled appointment [{$appointment->id}]"));

        return new AppointmentResource($appointment->fresh());
    }
}

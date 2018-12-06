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
            // Get the service user who originally booked to the appointment.
            $serviceUser = $appointment->serviceUser;

            // Cancel the appointment.
            $appointment->cancel();

            // Check if it was the service user who cancelled the appointment.
            $serviceUserInitiated = $request->user() === null;

            // Send notifications depending on who made the cancellation.
            if ($serviceUserInitiated) {
                if ($appointment->user->receive_cancellation_confirmations) {
                    $this->dispatch(new \App\Notifications\Email\CommunityWorker\BookingCancelledByServiceUserEmail($appointment));
                }

                $this->dispatch(new \App\Notifications\Sms\ServiceUser\BookingCancelledByServiceUserSms($appointment));

                if ($serviceUser->email) {
                    $this->dispatch(new \App\Notifications\Email\ServiceUser\BookingCancelledByServiceUserEmail($appointment));
                }
            } else {
                if ($appointment->user->receive_cancellation_confirmations) {
                    $this->dispatch(new \App\Notifications\Email\CommunityWorker\BookingCancelledByUserEmail($appointment));
                }

                $this->dispatch(new \App\Notifications\Sms\ServiceUser\BookingCancelledByUserSms($appointment));

                if ($serviceUser->email) {
                    $this->dispatch(new \App\Notifications\Email\ServiceUser\BookingCancelledByUserEmail($appointment));
                }
            }

            return $appointment;
        });

        event(EndpointHit::onUpdate($request, "Cancelled appointment [{$appointment->id}]"));

        return new AppointmentResource($appointment->fresh());
    }
}

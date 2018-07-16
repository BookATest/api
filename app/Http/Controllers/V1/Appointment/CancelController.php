<?php

namespace App\Http\Controllers\V1\Appointment;

use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CancelController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Appointment $appointment)
    {
        $userLoggedIn = $request->user() !== null;
        $serviceUserToken = $request->input('service_user_token', '');
        $appointmentBookedByServiceUser = $userLoggedIn
            ? false
            : $appointment->serviceUser->validateToken($serviceUserToken);

        // Don't allow the SU to cancel this appointment if it's not theirs.
        abort_if(
            !$userLoggedIn && !$appointmentBookedByServiceUser,
            Response::HTTP_FORBIDDEN
        );

        // Don't allow the CW to cancel this appointment if it's at a different clinic.
        abort_if(
            $userLoggedIn && !$request->user()->isCommunityWorker($appointment->clinic),
            Response::HTTP_FORBIDDEN
        );

        // Don't allow an appointment in the past to be cancelled.
        abort_if(
            $appointment->start_at->lessThan(now()),
            Response::HTTP_CONFLICT,
            'Cannot cancel appointments in the past'
        );

        return DB::transaction(function () use ($appointment) {
            $appointment->service_user_uuid = null;
            $appointment->save();

            return new AppointmentResource($appointment);
        });
    }
}

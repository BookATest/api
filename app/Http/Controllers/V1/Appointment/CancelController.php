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
        $appointment = DB::transaction(function () use ($appointment) {
            $appointment->service_user_id = null;
            $appointment->booked_at = null;
            $appointment->save();

            return $appointment;
        });

        event(EndpointHit::onUpdate($request, "Cancelled appointment [{$appointment->id}]"));

        return new AppointmentResource($appointment->fresh());
    }
}

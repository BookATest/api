<?php

namespace App\Http\Controllers\V1\Appointment;

use App\Events\EndpointHit;
use App\Http\Requests\Appointment\Cancel\UpdateRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CancelController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Appointment\Cancel\UpdateRequest $request
     * @param  \App\Models\Appointment $appointment
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, Appointment $appointment)
    {
        event(EndpointHit::onUpdate($request, "Cancelled appointment [$appointment->id]"));

        return DB::transaction(function () use ($appointment) {
            $appointment->service_user_id = null;
            $appointment->save();

            return new AppointmentResource($appointment);
        });
    }
}

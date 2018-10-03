<?php

namespace App\Http\Controllers\V1\Appointment;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\Schedule\DestroyRequest;
use App\Http\Responses\ResourceDeletedResponse;
use App\Models\Appointment;
use App\Models\AppointmentSchedule;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    /**
     * ScheduleController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * @param \App\Http\Requests\Appointment\Schedule\DestroyRequest $request
     * @param \App\Models\Appointment $appointment
     * @return \App\Http\Responses\ResourceDeletedResponse
     */
    public function destroy(DestroyRequest $request, Appointment $appointment)
    {
        // Return a 404 if the appointment does not belong to a schedule.
        if (!$appointment->hasSchedule()) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $appointmentScheduleId = $appointment->appointment_schedule_id;

        DB::transaction(function () use ($appointment) {
            // Delete any future unbooked appointments including the current, and also soft delete the schedule.
            $appointment->appointmentSchedule->deleteFrom($appointment->start_at);
        });

        event(EndpointHit::onDelete($request, "Deleted appointment schedule [{$appointmentScheduleId}]"));

        return new ResourceDeletedResponse(AppointmentSchedule::class);
    }
}

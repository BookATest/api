<?php

namespace App\Http\Controllers\V1\Appointment;

use App\Events\EndpointHit;
use App\Http\Requests\Appointment\Schedule\DestroyRequest;
use App\Http\Responses\ResourceDeletedResponse;
use App\Models\Appointment;
use App\Models\AppointmentSchedule;
use App\Http\Controllers\Controller;
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
     * Remove the specified resource from storage.
     *
     * @param \App\Http\Requests\Appointment\Schedule\DestroyRequest $request
     * @param  \App\Models\Appointment $appointment
     * @return \Illuminate\Http\Response
     */
    public function destroy(DestroyRequest $request, Appointment $appointment)
    {
        event(EndpointHit::onDelete($request, "Deleted appointment [$appointment->id] along with schedule"));

        return DB::transaction(function () use ($appointment) {
            // Soft delete the schedule.
            $appointment->appointmentSchedule()->delete();

            // Delete any future unbooked appointments including the current.
            Appointment::query()
                ->where('user_id', $appointment->user_id)
                ->where('appointment_schedule_id', $appointment->appointment_schedule_id)
                ->where('start_at', '>=', $appointment->start_at)
                ->whereNull('service_user_id')
                ->delete();

            return new ResourceDeletedResponse(AppointmentSchedule::class);
        });
    }
}

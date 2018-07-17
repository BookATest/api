<?php

namespace App\Http\Controllers\V1\Appointment;

use App\Events\EndpointHit;
use App\Http\Responses\ResourceDeletedResponse;
use App\Models\Appointment;
use App\Models\AppointmentSchedule;
use Illuminate\Http\Request;
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
     * @param \Illuminate\Http\Request $request
     * @param  \App\Models\Appointment $appointment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Appointment $appointment)
    {
        // Only allow community workers to delete their own appointment schedule.
        abort_if(
            $request->user()->id !== $appointment->user_id,
            Response::HTTP_FORBIDDEN
        );

        event(EndpointHit::onDelete($request, "Deleted appointment [$appointment->id] along with schedule"));

        return DB::transaction(function () use ($appointment) {
            // Soft delete the schedule.
            $appointment->appointmentSchedule()->delete();

            // Delete any future unbooked appointments including the current.
            Appointment::query()
                ->where('user_id', $appointment->user_id)
                ->where('appointment_schedule_id', $appointment->appointment_schedule_id)
                ->where('start_at', '>=', $appointment->start_at)
                ->whereNull('service_user_uuid')
                ->delete();

            return new ResourceDeletedResponse(AppointmentSchedule::class);
        });
    }
}

<?php

namespace App\Http\Controllers\V1;

use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Http\Responses\ResourceDeletedResponse;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    /**
     * AppointmentController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $appointments = Appointment::orderByDesc('created_at')->paginate();

        return AppointmentResource::collection($appointments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Appointment $appointment
     * @return \App\Http\Resources\AppointmentResource
     */
    public function show(Appointment $appointment)
    {
        return new AppointmentResource($appointment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAppointmentRequest $request
     * @param  \App\Models\Appointment $appointment
     * @return \App\Http\Resources\AppointmentResource
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $appointment = DB::transaction(function () use ($request, $appointment) {
            $appointment->did_not_attend = $request->input('did_not_attend');
            $appointment->save();

            return $appointment;
        });

        return new AppointmentResource($appointment);
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
        // Only allow community workers at the same clinic delete the appointment.
        abort_if(
            !$request->user()->isCommunityWorker($appointment->clinic),
            Response::HTTP_FORBIDDEN
        );

        // Don't allow booked appointments to be cancelled.
        abort_if(
            $appointment->isbooked(),
            Response::HTTP_CONFLICT,
            'The appointment must first be cancelled'
        );

        return DB::transaction(function () use ($appointment) {
            $appointment->delete();

            return new ResourceDeletedResponse(Appointment::class);
        });
    }
}

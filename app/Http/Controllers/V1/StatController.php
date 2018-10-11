<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stat\IndexRequest;

class StatController extends Controller
{
    /**
     * StatController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * @param \App\Http\Requests\Stat\IndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(IndexRequest $request)
    {
        $totalAppointments = $request->user()->appointmentsThisWeek();
        $appointmentsAvailable = $request->user()->appointmentsAvailable();
        $appointmentsBooked = $request->user()->appointmentsBooked();
        $attendanceRate = $request->user()->attendanceRateThisWeek();
        $didNotAttendRate = $request->user()->didNotAttendRateThisWeek();
        $startAt = today()->startOfWeek();
        $endAt = today()->endOfWeek();

        event(EndpointHit::onRead($request, 'Viewed stats'));

        return response()->json(['data' => [
            'total_appointments' => $totalAppointments,
            'appointments_available' => $appointmentsAvailable,
            'appointments_booked' => $appointmentsBooked,
            'attendance_rate' => is_float($attendanceRate) ? round($attendanceRate, 2) : null,
            'did_not_attend_rate' => is_float($didNotAttendRate) ? round($didNotAttendRate, 2) : null,
            'start_at' => $startAt->toDateString(),
            'end_at' => $endAt->toDateString(),
        ]]);
    }
}

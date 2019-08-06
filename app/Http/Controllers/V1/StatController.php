<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stat\IndexRequest;
use App\Models\Clinic;
use Illuminate\Support\Facades\Date;

class StatController extends Controller
{
    /**
     * StatController constructor.
     */
    public function __construct()
    {
        $this->middleware('throttle:60,1');
        $this->middleware('auth:api');
    }

    /**
     * @param \App\Http\Requests\Stat\IndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(IndexRequest $request)
    {
        $clinicIds = $request->input('filter', [])['clinic_id'] ?? null;
        $clinicIds = explode(',', $clinicIds);
        $clinics = Clinic::query()->whereIn('id', $clinicIds)->get();
        $clinics = $clinics->isNotEmpty() ? $clinics : null;

        $totalAppointments = $request->user('api')->appointmentsThisWeek($clinics);
        $appointmentsAvailable = $request->user('api')->appointmentsAvailable($clinics);
        $appointmentsBooked = $request->user('api')->appointmentsBooked($clinics);
        $attendanceRate = $request->user('api')->attendanceRateThisWeek($clinics);
        $didNotAttendRate = $request->user('api')->didNotAttendRateThisWeek($clinics);
        $startAt = Date::today()->startOfWeek();
        $endAt = Date::today()->endOfWeek();

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

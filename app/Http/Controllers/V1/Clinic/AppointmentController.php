<?php

namespace App\Http\Controllers\V1\Clinic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clinic\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Clinic;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    /**
     * AppointmentController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api')->only('store');
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Clinic $clinic
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request, Clinic $clinic)
    {
        $appointments = $clinic
            ->appointments()
            ->when(!$request->user(), function (Builder $query): Builder {
                return $query->available();
            })
            ->orderByDesc('created_at')
            ->paginate();

        return AppointmentResource::collection($appointments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Clinic\StoreAppointmentRequest $request
     * @param \App\Models\Clinic $clinic
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAppointmentRequest $request, Clinic $clinic)
    {
        return DB::transaction(function () use ($request, $clinic) {
            if ($request->is_repeating) {
                // TODO: Create appointment schedule if repeating.
            }

            // TODO: Set appointment schedule ID if repeating.
            $appointment = Appointment::create([
                'user_id' => $request->user()->id,
                'clinic_id' => $clinic->id,
                'start_at' => Carbon::createFromFormat(Carbon::ISO8601, $request->start_at),
            ]);

            return new AppointmentResource($appointment);
        });
    }
}

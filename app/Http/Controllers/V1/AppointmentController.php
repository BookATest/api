<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\DestroyRequest;
use App\Http\Requests\Appointment\IndexRequest;
use App\Http\Requests\Appointment\ShowRequest;
use App\Http\Requests\Appointment\StoreRequest;
use App\Http\Requests\Appointment\UpdateRequest;
use App\Http\Resources\AppointmentResource;
use App\Http\Responses\ResourceDeletedResponse;
use App\Models\Appointment;
use App\Models\AppointmentSchedule;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\Filter;
use Spatie\QueryBuilder\QueryBuilder;

class AppointmentController extends Controller
{
    /**
     * AppointmentController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api')->except('index', 'show');
    }

    /**
     * Display a listing of the resource.
     *
     * @param \App\Http\Requests\Appointment\IndexRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexRequest $request)
    {
        // Prepare the base query.
        $baseQuery = Appointment::query();

        // If a guest made the request, then limit to only available appointments.
        if (Auth::guest()) {
            $baseQuery = $baseQuery->available();
        }

        // Specify allowed modifications to the query via the GET parameters.
        $appointments = QueryBuilder::for($baseQuery)
            ->allowedFilters(
                Filter::exact('id'),
                Filter::exact('user_id'),
                Filter::exact('clinic_id'),
                Filter::exact('service_user_id'),
                Filter::scope('available'),
                Filter::scope('starts_after'),
                Filter::scope('starts_before')
            )
            ->defaultSort('-created_at')
            ->allowedSorts('created_at')
            ->paginate();

        event(EndpointHit::onRead($request, 'Listed all appointments'));

        return AppointmentResource::collection($appointments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Appointment\StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        $appointment = DB::transaction(function () use ($request): Appointment {
            $startAt = Carbon::createFromFormat(Carbon::ATOM, $request->start_at)
                ->second(0);

            // For repeating appointments.
            if ($request->is_repeating) {
                /** @var \App\Models\AppointmentSchedule $appointmentSchedule */
                $appointmentSchedule = AppointmentSchedule::create([
                    'user_id' => $request->user()->id,
                    'clinic_id' => $request->clinic_id,
                    'weekly_on' => $startAt->dayOfWeek,
                    'weekly_at' => $startAt->toTimeString(),
                ]);

                $daysToSkip = 0;
                $appointments = $appointmentSchedule->createAppointments($daysToSkip);

                return $appointments->first();
            }

            // For a single appointments.
            return Appointment::create([
                'user_id' => $request->user()->id,
                'clinic_id' => $request->clinic_id,
                'start_at' => $startAt,
            ]);
        });

        event(EndpointHit::onCreate($request, "Created appointment [{$appointment->id}]"));

        return (new AppointmentResource($appointment->fresh()))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Http\Requests\Appointment\ShowRequest $request
     * @param  \App\Models\Appointment $appointment
     * @return \App\Http\Resources\AppointmentResource
     */
    public function show(ShowRequest $request, Appointment $appointment)
    {
        // Prepare the base query.
        $baseQuery = Appointment::query()
            ->where('id', '=', $appointment->id);

        // Specify allowed modifications to the query via the GET parameters.
        $appointment = QueryBuilder::for($baseQuery)
            ->allowedAppends(
                'service_user_name',
                'user_first_name',
                'user_last_name',
                'user_email',
                'user_phone'
            )
            ->firstOrFail();

        event(EndpointHit::onRead($request, "Viewed appointment [{$appointment->id}]"));

        return new AppointmentResource($appointment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Appointment\UpdateRequest $request
     * @param  \App\Models\Appointment $appointment
     * @return \App\Http\Resources\AppointmentResource
     */
    public function update(UpdateRequest $request, Appointment $appointment)
    {
        $appointment = DB::transaction(function () use ($request, $appointment): Appointment {
            $appointment->update([
                'did_not_attend' => $request->did_not_attend,
            ]);

            return $appointment;
        });

        event(EndpointHit::onUpdate($request, "Updated appointment [{$appointment->id}]"));

        return new AppointmentResource($appointment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Http\Requests\Appointment\DestroyRequest $request
     * @param  \App\Models\Appointment $appointment
     * @return \App\Http\Responses\ResourceDeletedResponse
     */
    public function destroy(DestroyRequest $request, Appointment $appointment)
    {
        $appointmentId = $appointment->id;

        DB::transaction(function () use ($appointment) {
            $appointment->delete();
        });

        event(EndpointHit::onDelete($request, "Deleted appointment [{$appointmentId}]"));

        return new ResourceDeletedResponse(Appointment::class);
    }
}

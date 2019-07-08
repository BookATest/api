<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\ServiceUser;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceUser\Appointment\IndexRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\ServiceUser;
use Spatie\QueryBuilder\Filter;
use Spatie\QueryBuilder\QueryBuilder;

class AppointmentController extends Controller
{
    /**
     * AppointmentController constructor.
     */
    public function __construct()
    {
        $this->middleware('throttle:60,1');
    }

    /**
     * Display a listing of the resource.
     *
     * @param \App\Http\Requests\ServiceUser\Appointment\IndexRequest $request
     * @param \App\Models\ServiceUser $serviceUser
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexRequest $request, ServiceUser $serviceUser)
    {
        // Prepare the base query.
        $baseQuery = Appointment::query()
            ->where('service_user_id', $serviceUser->id)
            ->where('start_at', '>', now()->timezone('UTC'));

        // Specify allowed modifications to the query via the GET parameters.
        $appointments = QueryBuilder::for($baseQuery)
            ->allowedFilters(
                Filter::exact('id'),
                Filter::exact('user_id'),
                Filter::exact('clinic_id'),
                Filter::scope('available')
            )
            ->allowedAppends(
                'user_first_name',
                'user_last_name',
                'user_email',
                'user_phone'
            )
            ->defaultSort('start_at')
            ->allowedSorts('start_at')
            ->paginate(per_page($request->per_page));

        event(EndpointHit::onRead($request, "Listed appointments for service user [$serviceUser->id]"));

        return AppointmentResource::collection($appointments);
    }
}

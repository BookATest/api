<?php

namespace App\Http\Controllers\V1\ServiceUser;

use App\Events\EndpointHit;
use App\Http\Requests\ServiceUser\Appointment\IndexRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\ServiceUser;
use App\Http\Controllers\Controller;

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
     * @param \App\Http\Requests\ServiceUser\Appointment\IndexRequest $request
     * @param  \App\Models\ServiceUser $serviceUser
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexRequest $request, ServiceUser $serviceUser)
    {
        event(EndpointHit::onRead($request, "Viewed all appointments for service user [$serviceUser->id]"));

        $appointments = $serviceUser
            ->appointments()
            ->orderByDesc('created_at')
            ->paginate();

        return AppointmentResource::collection($appointments);
    }
}

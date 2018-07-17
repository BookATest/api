<?php

namespace App\Http\Controllers\V1\User;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Appointment\IndexRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\User;

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
     * @param \App\Http\Requests\User\Appointment\IndexRequest $request
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexRequest $request, User $user)
    {
        event(EndpointHit::onRead($request, "Viewed all appointments for user [$user->id]"));

        $appointments = $user->appointments()->orderByDesc('created_at')->paginate();

        return AppointmentResource::collection($appointments);
    }
}

<?php

namespace App\Http\Controllers\V1\User;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Models\User;
use Illuminate\Http\Request;

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
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request, User $user)
    {
        event(EndpointHit::onRead($request, "Viewed all appointments for user [$user->id]"));

        $appointments = $user->appointments()->orderByDesc('created_at')->paginate();

        return AppointmentResource::collection($appointments);
    }
}

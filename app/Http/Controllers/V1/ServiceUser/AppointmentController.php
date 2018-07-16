<?php

namespace App\Http\Controllers\V1\ServiceUser;

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
     * @param  \App\Models\ServiceUser  $serviceUser
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(ServiceUser $serviceUser)
    {
        $appointments = $serviceUser
            ->appointments()
            ->orderByDesc('created_at')
            ->paginate();

        return AppointmentResource::collection($appointments);
    }
}

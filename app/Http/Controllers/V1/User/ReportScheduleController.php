<?php

namespace App\Http\Controllers\V1\User;

use App\Events\EndpointHit;
use App\Http\Requests\User\ReportSchedule\IndexRequest;
use App\Http\Requests\User\ReportSchedule\StoreRequest;
use App\Http\Resources\ReportScheduleResource;
use App\Models\User;
use App\Http\Controllers\Controller;

class ReportScheduleController extends Controller
{
    /**
     * ReportScheduleController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @param \App\Http\Requests\User\ReportSchedule\IndexRequest $request
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexRequest $request, User $user)
    {
        event(EndpointHit::onRead($request, "Viewed all report schedules for user [$user->id]"));

        $reportSchedules = $user->reportSchedules;

        return ReportScheduleResource::collection($reportSchedules);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\User\ReportSchedule\StoreRequest  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request, User $user)
    {
        event(EndpointHit::onCreate($request, "Created report schedules for user [$user->id]"));
    }
}

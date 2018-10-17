<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Requests\ReportSchedule\{IndexRequest};
use App\Http\Resources\ReportScheduleResource;
use App\Models\ReportSchedule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\Filter;
use Spatie\QueryBuilder\QueryBuilder;

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
     * @param \App\Http\Requests\ReportSchedule\IndexRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexRequest $request)
    {
        // Prepare the base query.
        $baseQuery = ReportSchedule::query()
            ->where('user_id', '=', $request->user()->id);

        // Specify allowed modifications to the query via the GET parameters.
        $reportSchedules = QueryBuilder::for($baseQuery)
            ->allowedFilters(
                Filter::exact('id'),
                Filter::exact('clinic_id')
            )
            ->defaultSort('-created_at')
            ->allowedSorts('created_at')
            ->paginate();

        event(EndpointHit::onRead($request, "Listed all report schedules"));

        return ReportScheduleResource::collection($reportSchedules);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReportSchedule  $reportSchedule
     * @return \Illuminate\Http\Response
     */
    public function show(ReportSchedule $reportSchedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReportSchedule  $reportSchedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReportSchedule $reportSchedule)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReportSchedule  $reportSchedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReportSchedule $reportSchedule)
    {
        //
    }
}

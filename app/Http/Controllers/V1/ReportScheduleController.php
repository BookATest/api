<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Requests\ReportSchedule\DestroyRequest;
use App\Http\Requests\ReportSchedule\IndexRequest;
use App\Http\Requests\ReportSchedule\ShowRequest;
use App\Http\Requests\ReportSchedule\StoreRequest;
use App\Http\Resources\ReportScheduleResource;
use App\Http\Responses\ResourceDeletedResponse;
use App\Models\ReportSchedule;
use App\Models\ReportType;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\Filter;
use Spatie\QueryBuilder\QueryBuilder;

class ReportScheduleController extends Controller
{
    /**
     * ReportScheduleController constructor.
     */
    public function __construct()
    {
        $this->middleware('throttle:60,1');
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
            ->where('user_id', '=', $request->user('api')->id);

        // Specify allowed modifications to the query via the GET parameters.
        $reportSchedules = QueryBuilder::for($baseQuery)
            ->allowedFilters(
                Filter::exact('id'),
                Filter::exact('clinic_id')
            )
            ->defaultSort('-created_at')
            ->allowedSorts('created_at')
            ->paginate(per_page($request->per_page));

        event(EndpointHit::onRead($request, "Listed all report schedules"));

        return ReportScheduleResource::collection($reportSchedules);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\ReportSchedule\StoreRequest $request
     * @return \App\Http\Resources\ReportScheduleResource
     */
    public function store(StoreRequest $request)
    {
        $reportSchedule = DB::transaction(function () use ($request) {
            return ReportSchedule::create([
                'user_id' => $request->user('api')->id,
                'clinic_id' => $request->clinic_id,
                'report_type_id' => ReportType::findByName($request->report_type)->id,
                'repeat_type' => $request->repeat_type,
            ]);
        });

        event(EndpointHit::onCreate($request, "Created report schedule [$reportSchedule->id]"));

        return new ReportScheduleResource($reportSchedule);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Http\Requests\ReportSchedule\ShowRequest $request
     * @param  \App\Models\ReportSchedule $reportSchedule
     * @return \App\Http\Resources\ReportScheduleResource
     */
    public function show(ShowRequest $request, ReportSchedule $reportSchedule)
    {
        event(EndpointHit::onRead($request, "Viewed report schedule [$reportSchedule->id]"));

        return new ReportScheduleResource($reportSchedule);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Http\Requests\ReportSchedule\DestroyRequest $request
     * @param  \App\Models\ReportSchedule $reportSchedule
     * @return \App\Http\Responses\ResourceDeletedResponse
     */
    public function destroy(DestroyRequest $request, ReportSchedule $reportSchedule)
    {
        $reportScheduleId = $reportSchedule->id;

        DB::transaction(function () use ($reportSchedule) {
            $reportSchedule->delete();
        });

        event(EndpointHit::onDelete($request, "Deleted report schedule [$reportScheduleId]"));

        return new ResourceDeletedResponse(ReportSchedule::class);
    }
}

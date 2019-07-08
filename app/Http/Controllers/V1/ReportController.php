<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\DestroyRequest;
use App\Http\Requests\Report\IndexRequest;
use App\Http\Requests\Report\ShowRequest;
use App\Http\Requests\Report\StoreRequest;
use App\Http\Resources\ReportResource;
use App\Http\Responses\ResourceDeletedResponse;
use App\Models\Clinic;
use App\Models\Report;
use App\Models\ReportType;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\Filter;
use Spatie\QueryBuilder\QueryBuilder;

class ReportController extends Controller
{
    /**
     * ReportController constructor.
     */
    public function __construct()
    {
        $this->middleware('throttle:60,1');
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @param \App\Http\Requests\Report\IndexRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexRequest $request)
    {
        // Prepare the base query and limit to the only user's reports.
        $baseQuery = Report::query()
            ->with('reportType')
            ->where('user_id', '=', $request->user('api')->id);

        // If the user is an organisation admin, then show the global reports as well.
        if ($request->user('api')->isOrganisationAdmin()) {
            $baseQuery = $baseQuery->orWhereNull('user_id');
        }

        // Specify allowed modifications to the query via the GET parameters.
        $reports = QueryBuilder::for($baseQuery)
            ->allowedFilters(
                Filter::exact('id'),
                Filter::exact('clinic_id')
            )
            ->defaultSort('-created_at')
            ->allowedSorts('created_at')
            ->paginate(per_page($request->per_page));

        event(EndpointHit::onRead($request, 'Listed all reports'));

        return ReportResource::collection($reports);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Report\StoreRequest $request
     * @return \App\Http\Resources\ReportResource
     */
    public function store(StoreRequest $request)
    {
        $report = DB::transaction(function () use ($request) {
            return Report::createAndUpload(
                $request->user('api'),
                Clinic::find($request->clinic_id),
                ReportType::findByName($request->type),
                CarbonImmutable::createFromFormat('Y-m-d', $request->start_at),
                CarbonImmutable::createFromFormat('Y-m-d', $request->end_at)
            );
        });

        event(EndpointHit::onCreate($request, "Created report [$report->id]"));

        return new ReportResource($report);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Http\Requests\Report\ShowRequest $request
     * @param \App\Models\Report $report
     * @return \App\Http\Resources\ReportResource
     */
    public function show(ShowRequest $request, Report $report)
    {
        event(EndpointHit::onRead($request, "Viewed report [$report->id]"));

        return new ReportResource($report);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Http\Requests\Report\DestroyRequest $request
     * @param \App\Models\Report $report
     * @return \App\Http\Responses\ResourceDeletedResponse
     */
    public function destroy(DestroyRequest $request, Report $report)
    {
        $reportId = $report->id;

        DB::transaction(function () use ($report) {
            $report->delete();
        });

        event(EndpointHit::onDelete($request, "Deleted report [$reportId]"));

        return new ResourceDeletedResponse(Report::class);
    }
}

<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Requests\Report\{IndexRequest, StoreRequest};
use App\Http\Resources\ReportResource;
use App\Models\File;
use App\Models\Report;
use App\Models\ReportType;
use App\ReportGenerators\ReportGeneratorFactory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
            ->where('user_id', '=', $request->user()->id);

        // If the user is an organisation admin, then show the global reports as well.
        if ($request->user()->isOrganisationAdmin()) {
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
            ->paginate();

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
            // Create the file.
            $file = File::create([
                'filename' => "{$request->type}_{$request->start_at}-{$request->end_at}.xlsx",
                'mime_type' => File::MIME_XLSX,
            ]);

            // Create the report model.
            $report = Report::create([
                'user_id' => $request->user()->id,
                'file_id' => $file->id,
                'clinic_id' => $request->clinic_id,
                'report_type_id' => ReportType::findByName($request->type)->id,
                'start_at' => Carbon::createFromFormat('Y-m-d', $request->start_at),
                'end_at' => Carbon::createFromFormat('Y-m-d', $request->end_at),
            ]);

            // Generate the report.
            $file->upload(
                ReportGeneratorFactory::for($report)->generate()
            );

            return $report;
        });

        event(EndpointHit::onCreate($request, "Created report [$report->id]"));

        return new ReportResource($report);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function show(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function destroy(Report $report)
    {
        //
    }
}

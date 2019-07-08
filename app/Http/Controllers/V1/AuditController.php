<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Requests\Audit\IndexRequest;
use App\Http\Requests\Audit\ShowRequest;
use App\Http\Resources\AuditResource;
use App\Models\Audit;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\Filter;
use Spatie\QueryBuilder\QueryBuilder;

class AuditController extends Controller
{
    /**
     * AuditController constructor.
     */
    public function __construct()
    {
        $this->middleware('throttle:60,1');
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @param \App\Http\Requests\Audit\IndexRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexRequest $request)
    {
        // Prepare the base query.
        $baseQuery = Audit::query();

        // Specify allowed modifications to the query via the GET parameters.
        $audits = QueryBuilder::for($baseQuery)
            ->defaultSort('-created_at')
            ->allowedSorts('created_at')
            ->allowedFilters(
                Filter::exact('id')
            )
            ->paginate(per_page($request->per_page));

        event(EndpointHit::onRead($request, 'Listed all audits'));

        return AuditResource::collection($audits);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Http\Requests\Audit\ShowRequest $request
     * @param  \App\Models\Audit $audit
     * @return \App\Http\Resources\AuditResource
     */
    public function show(ShowRequest $request, Audit $audit)
    {
        event(EndpointHit::onRead($request, "Viewed audit [{$audit->id}]"));

        return new AuditResource($audit);
    }
}

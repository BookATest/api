<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Requests\Audit\IndexRequest;
use App\Http\Resources\AuditResource;
use App\Models\Audit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class AuditController extends Controller
{
    /**
     * AuditController constructor.
     */
    public function __construct()
    {
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
        event(EndpointHit::onRead($request, 'Viewed all audits'));

        $audits = Audit::orderByDesc('created_at')->paginate();

        return AuditResource::collection($audits);
    }
}

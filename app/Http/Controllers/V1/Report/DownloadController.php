<?php

namespace App\Http\Controllers\V1\Report;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\DownloadRequest;
use App\Models\Report;

class DownloadController extends Controller
{
    /**
     * DownloadController constructor.
     */
    public function __construct()
    {
        $this->middleware('throttle:60,1');
        $this->middleware('auth:api');
    }

    /**
     * @param \App\Http\Requests\Report\DownloadRequest $request
     * @param \App\Models\Report $report
     * @return \Illuminate\Http\Response
     */
    public function __invoke(DownloadRequest $request, Report $report)
    {
        event(EndpointHit::onRead($request, "Download report [$report->id]"));

        return $report->file;
    }
}

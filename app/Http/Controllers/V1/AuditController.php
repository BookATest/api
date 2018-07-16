<?php

namespace App\Http\Controllers\V1;

use App\Http\Resources\AuditResource;
use App\Models\Audit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class AuditController extends Controller
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
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        // Do not allow users who are not organisation admins to view the audits.
        abort_if(!$request->user()->isOrganisationAdmin(), Response::HTTP_FORBIDDEN);

        $audits = Audit::orderByDesc('created_at')->paginate();

        return AuditResource::collection($audits);
    }
}

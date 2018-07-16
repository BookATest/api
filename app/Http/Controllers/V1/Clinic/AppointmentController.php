<?php

namespace App\Http\Controllers\V1\Clinic;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Models\Clinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Clinic $clinic
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request, Clinic $clinic)
    {
        $appointments = $clinic
            ->appointments()
            ->when(!$request->user(), function (Builder $query): Builder {
                return $query->available();
            })
            ->orderByDesc('created_at')
            ->paginate();

        return AppointmentResource::collection($appointments);
    }
}

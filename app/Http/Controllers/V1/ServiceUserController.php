<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Requests\ServiceUser\IndexRequest;
use App\Http\Requests\ServiceUser\ShowRequest;
use App\Http\Resources\ServiceUserResource;
use App\Models\ServiceUser;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\Filter;
use Spatie\QueryBuilder\QueryBuilder;

class ServiceUserController extends Controller
{
    /**
     * ServiceUserController constructor.
     */
    public function __construct()
    {
        $this->middleware('throttle:60,1');
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @param \App\Http\Requests\ServiceUser\IndexRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexRequest $request)
    {
        // Prepare the base query.
        $baseQuery = ServiceUser::query();

        // Specify allowed modifications to the query via the GET parameters.
        $serviceUsers = QueryBuilder::for($baseQuery)
            ->allowedFilters(
                Filter::exact('id'),
                'name'
            )
            ->defaultSort('name')
            ->allowedSorts('name')
            ->paginate(per_page($request->per_page));

        event(EndpointHit::onRead($request, 'Listed all service users'));

        return ServiceUserResource::collection($serviceUsers);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Http\Requests\ServiceUser\ShowRequest $request
     * @param  \App\Models\ServiceUser $serviceUser
     * @return \App\Http\Resources\ServiceUserResource
     */
    public function show(ShowRequest $request, ServiceUser $serviceUser)
    {
        event(EndpointHit::onRead($request, "Viewed service user [{$serviceUser->id}]"));

        return new ServiceUserResource($serviceUser);
    }
}

<?php

namespace App\Http\Controllers\V1\ServiceUser;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceUser\Token\ShowRequest;
use App\Http\Requests\ServiceUser\Token\StoreRequest;
use App\Http\Resources\ServiceUserResource;
use App\Models\ServiceUser;
use Illuminate\Http\Response;

class TokenController extends Controller
{
    /**
     * TokenController constructor.
     */
    public function __construct()
    {
        $this->middleware('throttle:60,1');
    }

    /**
     * @param \App\Http\Requests\ServiceUser\Token\StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        $serviceUser = ServiceUser::findByAccessCode($request->access_code);

        event(EndpointHit::onCreate($request, "Requested token with access code [$request->access_code]"));

        return response()->json(['token' => $serviceUser->generateToken()]);
    }

    /**
     * @param \App\Http\Requests\ServiceUser\Token\ShowRequest $request
     * @param string $token
     * @return \App\Http\Resources\ServiceUserResource
     */
    public function show(ShowRequest $request, string $token)
    {
        if (!ServiceUser::validateToken($token)) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $serviceUser = ServiceUser::findByToken($token);

        event(EndpointHit::onRead($request, "Viewed service user [{$serviceUser->id}]"));

        return new ServiceUserResource($serviceUser);
    }
}

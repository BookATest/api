<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Requests\User\{IndexRequest, StoreRequest};
use App\Http\Resources\UserResource;
use App\Models\Clinic;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\Filter;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    /**
     * UserController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @param \App\Http\Requests\User\IndexRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexRequest $request)
    {
        // Prepare the base query.
        $baseQuery = User::query();

        // Specify allowed modifications to the query via the GET parameters.
        $users = QueryBuilder::for($baseQuery)
            ->allowedFilters(
                Filter::exact('id'),
                Filter::exact('clinic_id')
            )
            ->defaultSort(['first_name', 'last_name'])
            ->allowedSorts('first_name', 'last_name')
            ->paginate();

        event(EndpointHit::onRead($request, 'Listed all users'));

        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\User\StoreRequest $request
     * @return \App\Http\Resources\UserResource
     */
    public function store(StoreRequest $request)
    {
        $user = DB::transaction(function () use ($request) {
            // Create the user.
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
                'display_email' => $request->display_email,
                'display_phone' => $request->display_phone,
                'include_calendar_attachment' => $request->include_calendar_attachment,
                'calendar_feed_token' => User::generateCalendarFeedToken(),
            ]);

            // Create the roles.
            foreach ($request->roles as $role) {
                switch ($role['role']) {
                    case Role::COMMUNITY_WORKER:
                        $user->makeCommunityWorker(
                            Clinic::findOrFail($role['clinic_id'])
                        );
                        break;
                    case Role::CLINIC_ADMIN:
                        $user->makeClinicAdmin(
                            Clinic::findOrFail($role['clinic_id'])
                        );
                        break;
                    case Role::ORGANISATION_ADMIN:
                        $user->makeOrganisationAdmin();
                        break;
                }
            }

            // Upload the profile picture.
            if ($request->has('profile_picture')) {
                $profilePicture = $user->profilePictureFile()->create([
                    'filename' => 'profile-picture.png',
                    'mime_type' => 'image/png',
                ]);

                $profilePicture->uploadBase64EncodedPng($request->profile_picture);
            }

            return $user;
        });

        event(EndpointHit::onCreate($request, "Created user [{$user->id}]"));

        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}

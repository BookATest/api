<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\{IndexRequest, ShowRequest, StoreRequest, UpdateRequest};
use App\Http\Resources\UserResource;
use App\Models\Clinic;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
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
            $userRoles = UserRole::parseArray($request->roles);
            foreach ($userRoles as $userRole) {
                switch ($userRole->role->name) {
                    case Role::COMMUNITY_WORKER:
                        $user->makeCommunityWorker($userRole->clinic);
                        break;
                    case Role::CLINIC_ADMIN:
                        $user->makeClinicAdmin($userRole->clinic);
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
     * @param \App\Http\Requests\User\ShowRequest $request
     * @param  \App\Models\User $user
     * @return \App\Http\Resources\UserResource
     */
    public function show(ShowRequest $request, User $user)
    {
        event(EndpointHit::onRead($request, "Viewed user [{$user->id}]"));

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\User\UpdateRequest $request
     * @param  \App\Models\User $user
     * @return \App\Http\Resources\UserResource
     */
    public function update(UpdateRequest $request, User $user)
    {
        $user = DB::transaction(function () use ($request, $user) {
            // Update the user.
            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'display_email' => $request->display_email,
                'display_phone' => $request->display_phone,
                'include_calendar_attachment' => $request->include_calendar_attachment,
            ]);

            // If a password was given with the request, then update the password.
            if ($request->has('password')) {
                $user->update(['password' => bcrypt($request->password)]);
            }

            // Update the user roles.
            $userRoles = UserRole::parseArray($request->roles);
            $newRoles = $user->getAssignedRoles($userRoles);
            $deletedRoles = $user->getRevokedRoles($userRoles);

            // Assign the new roles.
            foreach ($newRoles as $newRole) {
                switch ($newRole->role->name) {
                    case Role::COMMUNITY_WORKER:
                        $user->makeCommunityWorker($newRole->clinic);
                        break;
                    case Role::CLINIC_ADMIN:
                        $user->makeClinicAdmin($newRole->clinic);
                        break;
                    case Role::ORGANISATION_ADMIN:
                        $user->makeOrganisationAdmin();
                        break;
                }
            }

            // Revoke the deleted roles.
            foreach ($deletedRoles as $deletedRole) {
                switch ($deletedRole->role->name) {
                    case Role::COMMUNITY_WORKER:
                        $user->revokeCommunityWorker($deletedRole->clinic);
                        break;
                    case Role::CLINIC_ADMIN:
                        $user->revokeClinicAdmin($deletedRole->clinic);
                        break;
                    case Role::ORGANISATION_ADMIN:
                        $user->revokeOrganisationAdmin();
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

        event(EndpointHit::onUpdate($request, "Updated user [{$user->id}]"));

        return new UserResource($user->fresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}

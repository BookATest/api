<?php

namespace App\Http\Controllers\V1\Appointment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\IcsRequest;
use App\Http\Responses\ICalAppointmentsResponse;
use App\Models\Appointment;
use App\Models\User;
use Spatie\QueryBuilder\Filter;
use Spatie\QueryBuilder\QueryBuilder;

class IcsController extends Controller
{
    /**]
     * @param \App\Http\Requests\Appointment\IcsRequest $request
     * @return \App\Http\Responses\ICalAppointmentsResponse
     */
    public function __invoke(IcsRequest $request)
    {
        $user = User::findByCalendarFeedToken($request->calendar_feed_token);

        // Prepare the base query.
        $baseQuery = Appointment::query()
            ->with('clinic')
            ->whereBetween('start_at', [today(), today()->addMonths(3)])
            ->orderBy('start_at');

        // If the user is not an organisation admin, then limit the results to the user's clinics.
        if (!$user->isOrganisationAdmin()) {
            $clinicIds = $user->userRoles()->pluck('clinic_id')->toArray();
            $baseQuery = $baseQuery->whereIn('clinic_id', $clinicIds);
        }

        // Specify allowed modifications to the query via the GET parameters.
        $appointments = QueryBuilder::for($baseQuery)
            ->allowedFilters(
                Filter::exact('id'),
                Filter::exact('user_id'),
                Filter::exact('clinic_id'),
                Filter::exact('service_user_id'),
                Filter::scope('available')
            );

        return new ICalAppointmentsResponse($appointments);
    }
}

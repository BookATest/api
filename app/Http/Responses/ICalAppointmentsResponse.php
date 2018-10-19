<?php

namespace App\Http\Responses;

use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Eluceo\iCal\Property\Event\Organizer;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;

class ICalAppointmentsResponse implements Responsable
{
    const MIME_ICS = 'text/calendar';
    const CUTYPE_INDIVIDUAL = 'INDIVIDUAL';

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * ICalAppointmentsResponse constructor.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        $calendar = (new Calendar(config('app.url')))
            ->setName(config('app.name') . ' Appointments')
            ->setDescription('A feed of appointments.');

        // Loop through each appointment and add as an event.
        $this->query->chunk(200, function (Collection $appointments) use ($calendar) {
            /** @var \App\Models\Appointment $appointment */
            foreach ($appointments as $appointment) {
                // Parse the appointment into an event.
                $event = (new Event($appointment->id))
                    ->setUniqueId($appointment->id)
                    ->setDtStart($appointment->start_at)
                    ->setDtEnd($appointment->start_at->addMinutes($appointment->clinic->appointment_duration))
                    ->setSummary("Appointment at {$appointment->clinic->name}")
                    ->setOrganizer(new Organizer("MAILTO:{$appointment->user->email}", [
                        'CN' => $appointment->user->full_name,
                        'CUTYPE' => static::CUTYPE_INDIVIDUAL,
                    ]));

                // Add the event to the calendar.
                $calendar->addComponent($event);
            }
        });

        return response()->make($calendar->render(), Response::HTTP_OK, [
            'Content-Type' => static::MIME_ICS,
            'Content-Disposition' => 'inline; filename="calendar-feed.ics"',
        ]);
    }
}

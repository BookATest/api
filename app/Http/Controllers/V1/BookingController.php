<?php

namespace App\Http\Controllers\V1;

use App\Contracts\Geocoder;
use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\EligibilityRequest;
use App\Http\Requests\Booking\StoreRequest;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\ClinicResource;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Question;
use App\Models\ServiceUser;
use App\Notifications\Email\ServiceUser\BookingConfirmedEmail as BookingConfirmedServiceUserEmail;
use App\Notifications\Email\CommunityWorker\BookingConfirmedEmail as BookingConfirmedUserEmail;
use App\Notifications\Sms\ServiceUser\BookingConfirmedSms as BookingConfirmedServiceUserSms;
use App\Support\Coordinate;
use App\Support\Postcode;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * BookingController constructor.
     */
    public function __construct()
    {
        $this->middleware('throttle:60,1');
    }

    /**
     * @param \App\Http\Requests\Booking\StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        $appointment = DB::transaction(function () use ($request) {
            // Create the service user.
            $serviceUser = ServiceUser::updateOrCreate(
                ['phone' => $request->service_user['phone']],
                [
                    'name' => $request->service_user['name'],
                    'email' => $request->service_user['email'],
                    'preferred_contact_method' => $request->service_user['preferred_contact_method'],
                ]
            );

            // Get and book the appointment.
            /** @var \App\Models\Appointment $appointment */
            $appointment = Appointment::findOrFail($request->appointment_id);
            $appointment->book($serviceUser);

            // Store the service user's answers.
            foreach ($request->answers as $answer) {
                // Retrieve the question model.
                $question = Question::findOrFail($answer['question_id']);

                switch ($question->type) {
                    case Question::SELECT:
                        $appointment->createSelectAnswer($question, $serviceUser, $answer['answer']);
                        break;
                    case Question::DATE:
                        $appointment->createDateAnswer(
                            $question,
                            $serviceUser,
                            CarbonImmutable::createFromFormat('Y-m-d', $answer['answer'])
                        );
                        break;
                    case Question::CHECKBOX:
                        $appointment->createCheckboxAnswer($question, $serviceUser, $answer['answer']);
                        break;
                    case Question::TEXT:
                        $appointment->createTextAnswer($question, $serviceUser, $answer['answer']);
                        break;
                }
            }

            // Dispatch the booking notifications.
            if ($appointment->user->receive_booking_confirmations) {
                $this->dispatch(new BookingConfirmedUserEmail($appointment));
            }

            $this->dispatch(new BookingConfirmedServiceUserSms($appointment));

            if (in_array($serviceUser->preferred_contact_method, [ServiceUser::EMAIL, ServiceUser::BOTH])) {
                $this->dispatch(new BookingConfirmedServiceUserEmail($appointment));
            }

            return $appointment;
        });

        event(EndpointHit::onCreate($request, "Created booking for appointment [$appointment->id]"));

        return (new AppointmentResource($appointment->fresh()))
            ->toResponse($request)
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param \App\Http\Requests\Booking\EligibilityRequest $request
     * @param \App\Contracts\Geocoder $geocoder
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function eligibility(EligibilityRequest $request, Geocoder $geocoder)
    {
        // Get all the valid clinics.
        $clinics = new Collection();

        Clinic::query()
            ->with('eligibleAnswers')
            ->chunk(200, function (Collection $chunkedClinics) use ($request, $clinics) {
                // Loop through each chunked clinic.
                foreach ($chunkedClinics as $clinic) {
                    // If the clinic is eligible based on the answers then append it to the collection.
                    if ($clinic->isEligible($request->answers)) {
                        $clinics->push($clinic);
                    }
                }
            });

        // Get the coordinate for the location or postcode.
        $coordinate = $request->has('location')
            ? new Coordinate($request->input('location.lat'), $request->input('location.lon'))
            : $geocoder->geocode(new Postcode($request->postcode));

        // Order the clinics by distance.
        $clinics = $clinics->sortBy(function (Clinic $clinic) use ($coordinate) {
            // If the location does not have a coordinate, then set the distance to the PHP max integer size.
            return optional($clinic->coordinate())->distanceFrom($coordinate) ?? PHP_INT_MAX;
        })->values();

        // Append the distance to the clinics.
        $clinics->each(function (Clinic $clinic) use ($coordinate) {
            $clinic->distance = optional($clinic->coordinate())->distanceFrom($coordinate) ?? null;
            $clinic->append('distance');
        });

        // Return the clinics.
        return ClinicResource::collection($clinics);
    }
}

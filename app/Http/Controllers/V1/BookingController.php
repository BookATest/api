<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Question;
use App\Models\ServiceUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
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
                        $appointment->createDateAnswer($question, $serviceUser, new Carbon($answer['answer']));
                        break;
                    case Question::CHECKBOX:
                        $appointment->createCheckboxAnswer($question, $serviceUser, $answer['answer']);
                        break;
                    case Question::TEXT:
                        $appointment->createTextAnswer($question, $serviceUser, $answer['answer']);
                        break;
                }
            }

            return $appointment;
        });

        event(EndpointHit::onCreate($request, "Created booking for appointment [$appointment->id]"));

        return (new AppointmentResource($appointment->fresh()))
            ->toResponse($request)
            ->setStatusCode(Response::HTTP_CREATED);
    }
}

<?php

namespace App\Http\Controllers\V1\Appointment;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\CancelRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

class CancelController extends Controller
{
    /**
     * @param \App\Http\Requests\Appointment\CancelRequest $request
     * @param \App\Models\Appointment $appointment
     * @return \App\Http\Resources\AppointmentResource
     */
    public function __invoke(CancelRequest $request, Appointment $appointment)
    {
        $appointment = DB::transaction(function () use ($request, $appointment) {
            // Get the service user who originally booked to the appointment.
            $serviceUser = $appointment->serviceUser;

            // Cancel the appointment.
            $appointment->cancel();

            // Check if it was the service user who cancelled the appointment.
            $serviceUserInitiated = $request->user() === null;

            // Send notifications depending on who made the cancellation.
            if ($serviceUserInitiated) {
                // Clinic admin emails.
                if ($appointment->clinic->send_cancellation_confirmations) {
                    foreach ($appointment->clinic->clinicAdmins as $clinicAdmin) {
                        $this->dispatch(
                            new \App\Notifications\Email\ClinicAdmin\BookingCancelledByServiceUserEmail(
                                $appointment,
                                $clinicAdmin
                            )
                        );
                    }
                }

                // Community worker email.
                if ($appointment->user->receive_cancellation_confirmations) {
                    $this->dispatch(
                        new \App\Notifications\Email\CommunityWorker\BookingCancelledByServiceUserEmail($appointment)
                    );
                }

                // Service user SMS.
                $this->dispatch(
                    new \App\Notifications\Sms\ServiceUser\BookingCancelledByServiceUserSms($appointment)
                );

                // Service user email.
                if ($serviceUser->email) {
                    $this->dispatch(
                        new \App\Notifications\Email\ServiceUser\BookingCancelledByServiceUserEmail($appointment)
                    );
                }
            } else {
                // Clinic admin emails.
                if ($appointment->clinic->send_cancellation_confirmations) {
                    foreach ($appointment->clinic->clinicAdmins as $clinicAdmin) {
                        $this->dispatch(
                            new \App\Notifications\Email\ClinicAdmin\BookingCancelledByUserEmail(
                                $appointment,
                                $clinicAdmin
                            )
                        );
                    }
                }

                // Community worker email.
                if ($appointment->user->receive_cancellation_confirmations) {
                    $this->dispatch(
                        new \App\Notifications\Email\CommunityWorker\BookingCancelledByUserEmail($appointment)
                    );
                }

                // Service user SMS.
                $this->dispatch(
                    new \App\Notifications\Sms\ServiceUser\BookingCancelledByUserSms($appointment)
                );

                // Service user email.
                if ($serviceUser->email) {
                    $this->dispatch(
                        new \App\Notifications\Email\ServiceUser\BookingCancelledByUserEmail($appointment)
                    );
                }
            }

            return $appointment;
        });

        event(EndpointHit::onUpdate($request, "Cancelled appointment [{$appointment->id}]"));

        return new AppointmentResource($appointment->fresh());
    }
}

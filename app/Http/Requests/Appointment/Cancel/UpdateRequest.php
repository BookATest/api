<?php

namespace App\Http\Requests\Appointment\Cancel;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $appointment = $this->route('appointment');
        $userLoggedIn = $this->user() !== null;
        $serviceUserToken = $this->input('service_user_token', '');
        $appointmentBookedByServiceUser = $userLoggedIn
            ? false
            : $appointment->serviceUser->validateToken($serviceUserToken);

        // Don't allow the SU to cancel this appointment if it's not theirs.
        if (!$userLoggedIn && !$appointmentBookedByServiceUser) {
            return false;
        }

        // Don't allow the CW to cancel this appointment if it's at a different clinic.
        if ($userLoggedIn && !$this->user()->isCommunityWorker($appointment->clinic)) {
            return false;
        }

        // Don't allow an appointment in the past to be cancelled.
        if ($appointment->start_at->lessThan(now())) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}

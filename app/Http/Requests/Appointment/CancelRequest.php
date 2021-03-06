<?php

namespace App\Http\Requests\Appointment;

use App\Rules\ServiceUserTokenIsValid;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Date;

class CancelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // If the appointment is in the past.
        if ($this->appointment->start_at->lessThan(Date::now())) {
            return false;
        }

        // If the appointment is available.
        if (!$this->appointment->is_booked) {
            return false;
        }

        // If an authenticated user is making the request for a clinic they do not belong to.
        if ($this->user('api') && !$this->user('api')->isCommunityWorker($this->appointment->clinic)) {
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
        // For authenticated users.
        if ($this->user('api')) {
            return [
                //
            ];
        }

        // For guests.
        return [
            'service_user_token' => [
                'required',
                new ServiceUserTokenIsValid($this->appointment->serviceUser),
            ],
        ];
    }
}

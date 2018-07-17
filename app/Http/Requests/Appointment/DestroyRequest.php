<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class DestroyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $appointment = $this->route('appointment');

        // Only allow community workers at the same clinic delete the appointment.
        if (!$this->user()->isCommunityWorker($appointment->clinic)) {
            return false;
        }

        // Don't allow booked appointments to be cancelled.
        if ($appointment->isbooked()) {
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

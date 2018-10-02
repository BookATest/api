<?php

namespace App\Http\Requests\Appointment;

use App\Rules\AppointmentMustBeBooked;
use App\Rules\IsCommunityWorkerForClinic;
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
        if (!$this->user()->isCommunityWorker($this->appointment->clinic)) {
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
            'did_not_attend' => [
                'required',
                'boolean',
                new AppointmentMustBeBooked($this->appointment),
            ],
        ];
    }
}

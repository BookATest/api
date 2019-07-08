<?php

declare(strict_types=1);

namespace App\Http\Requests\Appointment;

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
        // If the appointment is available.
        if (!$this->appointment->is_booked) {
            return false;
        }

        if (!$this->user('api')->isCommunityWorker($this->appointment->clinic)) {
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
            ],
        ];
    }
}

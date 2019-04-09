<?php

namespace App\Http\Requests\Appointment;

use App\Models\Clinic;
use App\Rules\AppointmentDoesntOverlap;
use App\Rules\AppointmentIsWithinSlot;
use App\Rules\AppointmentOutsideDstTransition;
use App\Rules\DateFormat;
use App\Rules\IsCommunityWorkerForClinic;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (!$this->user('api')->isCommunityWorker()) {
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
        $user = $this->user('api');
        $clinic = Clinic::find($this->clinic_id);

        return [
            'clinic_id' => [
                'required',
                'exists:clinics,id',
                new IsCommunityWorkerForClinic($user, $clinic),
            ],
            'start_at' => [
                'required',
                DateFormat::iso8601(),
                new AppointmentOutsideDstTransition(),
                new AppointmentDoesntOverlap($user, $clinic),
                new AppointmentIsWithinSlot($user, $clinic),
            ],
            'is_repeating' => [
                'required',
                'boolean',
            ],
        ];
    }
}

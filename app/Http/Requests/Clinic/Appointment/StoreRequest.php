<?php

namespace App\Http\Requests\Clinic\Appointment;

use App\Rules\AppointmentDoesntOverlap;
use App\Rules\AppointmentIsWithinSlot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->isCommunityWorker($this->route('clinic'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = $this->user();
        $clinic = $this->route('clinic');
        $startAt = Carbon::createFromFormat(Carbon::ISO8601, $this->start_at)->second(0);

        return [
            'start_at' => [
                'required',
                'date_format:'.Carbon::ISO8601,
                'after:today',
                new AppointmentDoesntOverlap($user, $clinic, $startAt),
                new AppointmentIsWithinSlot($user, $clinic, $startAt),
            ],
            'is_repeating' => ['required', 'boolean'],
        ];
    }
}

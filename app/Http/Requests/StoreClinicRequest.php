<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClinicRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (!$this->user()->isOrganisationAdmin()) {
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
            'name' => ['required', 'max:255'],
            'phone' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'address_line_1' => ['required', 'max:255'],
            'address_line_2' => ['nullable', 'max:255'],
            'address_line_3' => ['nullable', 'max:255'],
            'city' => ['required', 'max:255'],
            'postcode' => ['required', 'max:255'],
            'directions' => ['required'],
            'appointment_duration' => ['nullable', 'integer'],
            'appointment_booking_threshold' => ['nullable', 'integer'],
        ];
    }
}

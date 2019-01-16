<?php

namespace App\Http\Requests\Clinic;

use App\Rules\DurationFitsIntoDay;
use App\Rules\Postcode;
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
        if (!$this->user('api')->isOrganisationAdmin()) {
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
            'name' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
            'phone' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'address_line_1' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
            'address_line_2' => [
                'present',
                'nullable',
                'string',
                'min:1',
                'max:255',
            ],
            'address_line_3' => [
                'present',
                'nullable',
                'string',
                'min:1',
                'max:255',
            ],
            'city' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
            'postcode' => [
                'required',
                'string',
                'min:1',
                'max:255',
                new Postcode(),
            ],
            'directions' => [
                'required',
                'string',
                'min:1',
                'max:10000',
            ],
            'appointment_duration' => [
                'required',
                'integer',
                'min:1',
                'max:1440', // Total minutes in a day
                new DurationFitsIntoDay(),
            ],
            'appointment_booking_threshold' => [
                'required',
                'integer',
                'min:0',
            ],
            'send_cancellation_confirmations' => [
                'required',
                'boolean',
            ],
            'send_dna_follow_ups' => [
                'required',
                'boolean',
            ],
        ];
    }
}

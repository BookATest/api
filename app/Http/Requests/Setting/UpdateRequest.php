<?php

namespace App\Http\Requests\Setting;

use App\Rules\HexColour;
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
            'default_appointment_booking_threshold' => [
                'required',
                'integer',
                'min:0',
                'max:120',
            ],
            'default_appointment_duration' => [
                'required',
                'integer',
                'min:1',
                'max:1440',
            ],
            'language' => [
                'required',
                'array',
            ],
            'language.booking_questions_help_text' => [
                'required',
                'string',
            ],
            'language.booking_notification_help_text' => [
                'required',
                'string',
            ],
            'language.booking_enter_details_help_text' => [
                'required',
                'string',
            ],
            'language.booking_find_location_help_text' => [
                'required',
                'string',
            ],
            'language.booking_appointment_overview_help_text' => [
                'required',
                'string',
            ],
            'logo_file_id' => [
                'present',
                'nullable',
                'string',
                'exists:files,id',
            ],
            'name' => [
                'required',
                'string',
            ],
            'primary_colour' => [
                'required',
                'string',
                new HexColour(),
            ],
            'secondary_colour' => [
                'required',
                'string',
                new HexColour(),
            ],
        ];
    }
}

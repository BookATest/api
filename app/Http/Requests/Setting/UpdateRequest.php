<?php

namespace App\Http\Requests\Setting;

use App\Rules\Base64EncodedPng;
use App\Rules\HexColour;
use App\Rules\UkPhoneNumber;
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
        return array_merge([
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
            'logo' => [
                new Base64EncodedPng(),
            ],
            'name' => [
                'required',
                'string',
            ],
            'email' => [
                'required',
                'string',
                'email',
            ],
            'phone' => [
                'required',
                'string',
                new UkPhoneNumber(),
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
            'styles' => [
                'present',
                'nullable',
                'string',
            ],
        ], $this->languageRules());
    }

    /**
     * @return array
     */
    protected function languageRules(): array
    {
        return [
            'language' => [
                'required',
                'array',
            ],

            'language.home.title' => [
                'required',
                'string',
            ],
            'language.home.content' => [
                'present',
                'nullable',
                'string',
            ],


            'language.make-booking.introduction.title' => [
                'required',
                'string',
            ],
            'language.make-booking.introduction.content' => [
                'present',
                'nullable',
                'string',
            ],

            'language.make-booking.questions.title' => [
                'required',
                'string',
            ],
            'language.make-booking.questions.content' => [
                'present',
                'nullable',
                'string',
            ],

            'language.make-booking.location.title' => [
                'required',
                'string',
            ],
            'language.make-booking.location.content' => [
                'present',
                'nullable',
                'string',
            ],

            'language.make-booking.clinics.title' => [
                'required',
                'string',
            ],
            'language.make-booking.clinics.content' => [
                'present',
                'nullable',
                'string',
            ],

            'language.make-booking.appointments.title' => [
                'required',
                'string',
            ],
            'language.make-booking.appointments.content' => [
                'present',
                'nullable',
                'string',
            ],

            'language.make-booking.user-details.title' => [
                'required',
                'string',
            ],
            'language.make-booking.user-details.content' => [
                'present',
                'nullable',
                'string',
            ],

            'language.make-booking.consent.title' => [
                'required',
                'string',
            ],
            'language.make-booking.consent.content' => [
                'present',
                'nullable',
                'string',
            ],

            'language.make-booking.overview.title' => [
                'required',
                'string',
            ],
            'language.make-booking.overview.content' => [
                'present',
                'nullable',
                'string',
            ],

            'language.make-booking.confirmation.title' => [
                'required',
                'string',
            ],
            'language.make-booking.confirmation.content' => [
                'present',
                'nullable',
                'string',
            ],

            'language.list-bookings.access-code.title' => [
                'required',
                'string',
            ],
            'language.list-bookings.access-code.content' => [
                'present',
                'nullable',
                'string',
            ],

            'language.list-bookings.token.title' => [
                'required',
                'string',
            ],
            'language.list-bookings.token.content' => [
                'present',
                'nullable',
                'string',
            ],

            'language.list-bookings.appointments.title' => [
                'required',
                'string',
            ],
            'language.list-bookings.appointments.content' => [
                'present',
                'nullable',
                'string',
            ],
            'language.list-bookings.appointments.disclaimer' => [
                'required',
                'string',
            ],

            'language.list-bookings.cancel.title' => [
                'required',
                'string',
            ],
            'language.list-bookings.cancel.content' => [
                'present',
                'nullable',
                'string',
            ],

            'language.list-bookings.cancelled.title' => [
                'required',
                'string',
            ],
            'language.list-bookings.cancelled.content' => [
                'present',
                'nullable',
                'string',
            ],

            'language.list-bookings.token-expired.title' => [
                'required',
                'string',
            ],
            'language.list-bookings.token-expired.content' => [
                'present',
                'nullable',
                'string',
            ],
        ];
    }
}

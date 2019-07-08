<?php

declare(strict_types=1);

namespace App\Http\Requests\Booking;

use App\Models\Appointment;
use App\Rules\AllAnswersPresent;
use App\Rules\AppointmentAvailable;
use App\Rules\UkPhoneNumber;
use App\Rules\ValidAnswerForAppointment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Only allow email if the user has provided an email.
        $preferredContactMethods = ['phone'];
        $serviceUser = $this->get('service_user', []);
        $email = $serviceUser['email'] ?? null;

        if (is_string($email)) {
            $preferredContactMethods[] = 'email';
            $preferredContactMethods[] = 'both';
        }

        return [
            'appointment_id' => [
                'required',
                'exists:appointments,id',
                new AppointmentAvailable(),
            ],
            'service_user' => [
                'required',
                'array',
            ],
            'service_user.name' => [
                'required',
                'string',
                'max:255',
            ],
            'service_user.phone' => [
                'required',
                'string',
                new UkPhoneNumber(),
            ],
            'service_user.email' => [
                'present',
                'nullable',
                'email',
                'max:255',
            ],
            'service_user.preferred_contact_method' => [
                'required',
                Rule::in($preferredContactMethods),
            ],
            'answers' => [
                'required',
                'array',
                new AllAnswersPresent(),
            ],
            'answers.*' => [
                'required',
                'array',
                new ValidAnswerForAppointment(Appointment::find($this->appointment_id)),
            ],
            'answers.*.question_id' => [
                'required',
                'exists:questions,id',
            ],
            'answers.*.answer' => [
                'required',
            ],
        ];
    }
}

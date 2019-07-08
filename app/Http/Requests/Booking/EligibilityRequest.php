<?php

namespace App\Http\Requests\Booking;

use App\Models\Appointment;
use App\Rules\AllAnswersPresent;
use App\Rules\Postcode;
use App\Rules\ValidAnswer;
use Illuminate\Foundation\Http\FormRequest;

class EligibilityRequest extends FormRequest
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
        return [
            'postcode' => [
                'required_without:location',
                'string',
                new Postcode(),
            ],
            'location' => [
                'required_without:postcode',
                'array',
            ],
            'location.lat' => [
                'required_with:location',
                'numeric',
                'min:-90',
                'max:90',
            ],
            'location.lon' => [
                'required_with:location',
                'numeric',
                'min:-180',
                'max:180',
            ],
            'answers' => [
                'required',
                'array',
                new AllAnswersPresent(),
            ],
            'answers.*' => [
                'required',
                'array',
                new ValidAnswer(),
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

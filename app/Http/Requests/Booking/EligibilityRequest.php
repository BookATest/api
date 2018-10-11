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
                'required',
                'string',
                new Postcode(),
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

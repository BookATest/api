<?php

namespace App\Http\Requests\EligibleAnswer;

use App\Rules\AllAnswersPresent;
use App\Rules\ValidEligibleAnswer;
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
        return $this->user('api')->isClinicAdmin($this->clinic);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'answers' => [
                'required',
                'array',
                new AllAnswersPresent(),
            ],
            'answers.*' => [
                'required',
                'array',
                new ValidEligibleAnswer(),
            ],
            'answers.*.question_id' => [
                'required',
                'exists:questions,id',
            ],
            'answers.*.answer' => [
                'present',
            ],
        ];
    }
}

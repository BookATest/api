<?php

namespace App\Http\Requests\EligibleAnswer;

use App\Rules\ValidAnswer;
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
        return $this->user()->isClinicAdmin($this->clinic);
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

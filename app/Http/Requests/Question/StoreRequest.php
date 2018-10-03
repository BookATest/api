<?php

namespace App\Http\Requests\Question;

use App\Models\Question;
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
            'questions' => [
                'required',
                'array',
            ],
            'questions.*' => [
                'required',
                'array',
            ],
            'questions.*.question' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
            'questions.*.type' => [
                'required',
                'string',
                Rule::in([Question::SELECT, Question::TEXT, Question::DATE, Question::CHECKBOX]),
            ],
            'questions.*.options' => [
                'required_if:questions.*.type,' . Question::SELECT,
                'array',
            ],
            'questions.*.options.*' => [
                'required_if:questions.*.type,' . Question::SELECT,
                'string',
                'min:1',
                'max:255',
            ],
        ];
    }
}

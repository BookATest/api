<?php

namespace App\Http\Requests\Question;

use App\Models\Question;
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
        // Only allow organisation admins to create a new set of questions.
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
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question' => ['required', 'max:255'],
            'questions.*.type' => ['required', 'in:'.implode(',', [Question::SELECT, Question::CHECKBOX, Question::DATE, Question::TEXT])],
            'questions.*.options' => ['required_if:questions.*.type,'.Question::SELECT, 'array'],
            'questions.*.options.*' => ['max:255'],
        ];
    }
}

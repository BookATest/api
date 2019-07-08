<?php

namespace App\Http\Requests\Report;

use App\Rules\DateFormat;
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
        return $this->user('api')->isCommunityWorker();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clinic_id' => [
                'present',
                'nullable',
                'exists:clinics,id',
            ],
            'type' => [
                'required',
                'exists:report_types,name',
            ],
            'start_at' => [
                'required',
                DateFormat::date(),
                'before:end_at',
            ],
            'end_at' => [
                'required',
                DateFormat::date(),
                'after:start_at',
            ],
        ];
    }
}

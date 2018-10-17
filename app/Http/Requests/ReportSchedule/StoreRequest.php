<?php

namespace App\Http\Requests\ReportSchedule;

use App\Models\Clinic;
use App\Models\ReportSchedule;
use App\Rules\IsCommunityWorkerForClinic;
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
        return $this->user()->isCommunityWorker();
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
                new IsCommunityWorkerForClinic($this->user(), Clinic::find($this->clinic_id)),
            ],
            'report_type' => [
                'required',
                'exists:report_types,name',
            ],
            'repeat_type' => [
                'required',
                Rule::in([ReportSchedule::WEEKLY, ReportSchedule::MONTHLY]),
            ],
        ];
    }
}

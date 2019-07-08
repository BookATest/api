<?php

declare(strict_types=1);

namespace App\Http\Requests\Appointment\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class DestroyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // If an authenticated user is making the request for a clinic they do not belong to.
        if ($this->user('api') && !$this->user('api')->isCommunityWorker($this->appointment->clinic)) {
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
            //
        ];
    }
}

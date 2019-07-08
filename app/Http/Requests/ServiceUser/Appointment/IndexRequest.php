<?php

declare(strict_types=1);

namespace App\Http\Requests\ServiceUser\Appointment;

use App\Rules\ServiceUserTokenIsValid;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
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
            'service_user_token' => [
                'required',
                new ServiceUserTokenIsValid($this->service_user),
            ],
        ];
    }
}

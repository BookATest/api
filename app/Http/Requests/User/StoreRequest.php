<?php

namespace App\Http\Requests\User;

use App\Models\Role;
use App\Rules\Base64EncodedPng;
use App\Rules\CanAddRole;
use App\Rules\Password;
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
        if (!$this->user()->isClinicAdmin()) {
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
            'first_name' => [
                'required',
                'string',
                'max:255',
            ],
            'last_name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'phone' => [
                'required',
                'string',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
                'max:255',
                new Password(),
            ],
            'display_email' => [
                'required',
                'boolean',
            ],
            'display_phone' => [
                'required',
                'boolean',
            ],
            'include_calendar_attachment' => [
                'required',
                'boolean',
            ],
            'roles' => [
                'present',
                'array',
            ],
            'roles.*' => [
                'array',
                new CanAddRole($this->user()),
            ],
            'roles.*.role' => [
                'required_with:roles.*',
                'exists:roles,name',
            ],
            'roles.*.clinic_id' => [
                'required_if:roles.*.role,' . Role::COMMUNITY_WORKER,
                'required_if:roles.*.role,' . Role::CLINIC_ADMIN,
                'exists:clinics,id',
            ],
            'profile_picture' => [
                new Base64EncodedPng(),
            ],
        ];
    }
}

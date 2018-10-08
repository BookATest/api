<?php

namespace App\Http\Requests\User;

use App\Models\Role;
use App\Rules\Base64EncodedPng;
use App\Rules\CanAddRole;
use App\Rules\CanRemoveRoles;
use App\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->user()->id === $this->route('user')->id) {
            return true;
        }

        if ($this->user()->isClinicAdmin()) {
            return true;
        }

        return false;
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
                Rule::unique('users', 'email')->ignoreModel($this->user()),
            ],
            'phone' => [
                'required',
                'string',
                'max:255',
            ],
            'password' => [
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
                'required',
                'array',
                new CanRemoveRoles($this->user(), $this->route('user')),
            ],
            'roles.*' => [
                'required',
                'array',
                new CanAddRole($this->user(), $this->route('user')),
            ],
            'roles.*.role' => [
                'required',
                'exists:roles,name',
            ],
            'roles.*.clinic_id' => [
                'required_unless:roles.*.role,' . Role::ORGANISATION_ADMIN,
                'exists:clinics,id',
            ],
            'profile_picture' => [
                new Base64EncodedPng(),
            ],
        ];
    }
}
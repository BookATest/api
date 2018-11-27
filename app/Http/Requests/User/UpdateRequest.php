<?php

namespace App\Http\Requests\User;

use App\Models\Role;
use App\Rules\Base64EncodedJpeg;
use App\Rules\Base64FileSize;
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
        /** @var \App\Models\User $requestingUser */
        $requestingUser = $this->user();

        /** @var \App\Models\User $subjectUser */
        $subjectUser = $this->route('user');

        // If the user is updating their own profile.
        if ($requestingUser->id === $subjectUser->id) {
            return true;
        }

        // Always allow if the user is an organisation admin.
        if ($requestingUser->isOrganisationAdmin()) {
            return true;
        }

        // Only allow if the user is a clinic admin and the subject is not.
        if ($requestingUser->isClinicAdmin() && !$subjectUser->isClinicAdmin()) {
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
                Rule::unique('users', 'email')->ignoreModel($this->route('user')),
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
            'receive_booking_confirmations' => [
                'required',
                'boolean',
            ],
            'receive_cancellation_confirmations' => [
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
                new CanRemoveRoles($this->user(), $this->route('user')),
            ],
            'roles.*' => [
                'array',
                new CanAddRole($this->user(), $this->route('user')),
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
                new Base64EncodedJpeg(),
                new Base64FileSize(1),
            ],
        ];
    }
}

<?php

namespace App\Rules;

use App\Models\Clinic;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Throwable;

class CanAddRole implements Rule
{
    /**
     * @var \App\Models\User
     */
    protected $user;

    /**
     * Create a new rule instance.
     *
     * @param \App\Models\User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $role
     * @return bool
     */
    public function passes($attribute, $role)
    {
        if (!is_array($role)) {
            return false;
        }

        try {
            switch ($role['role']) {
                case Role::COMMUNITY_WORKER:
                    return $this->user->canMakeCommunityWorker(
                        Clinic::findOrFail($role['clinic_id'])
                    );
                case Role::CLINIC_ADMIN:
                    return $this->user->canMakeClinicAdmin(
                        Clinic::findOrFail($role['clinic_id'])
                    );
                case Role::ORGANISATION_ADMIN:
                    return $this->user->canMakeOrganisationAdmin();
            }
        } catch (Throwable $throwable) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'You\'re not authorised to add this role.';
    }
}

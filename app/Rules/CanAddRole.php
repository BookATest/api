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
     * @var \App\Models\User|null
     */
    protected $subjectUser;

    /**
     * Create a new rule instance.
     *
     * @param \App\Models\User $requestingUser
     * @param \App\Models\User|null $subjectUser
     */
    public function __construct(User $requestingUser, User $subjectUser = null)
    {
        $this->user = $requestingUser;
        $this->subjectUser = $subjectUser;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $role
     * @return bool
     */
    public function passes($attribute, $role)
    {
        if (!is_array($role)) {
            return false;
        }

        try {
            // Prepare variables.
            $isNewUser = $this->subjectUser === null;
            $roleName = $role['role'];
            $clinic = isset($role['clinic_id']) ? Clinic::findOrFail($role['clinic_id']) : null;

            return $isNewUser
                ? $this->passesForNewUser($roleName, $clinic)
                : $this->passesForExistingUser($roleName, $clinic);
        } catch (Throwable $throwable) {
            return false;
        }
    }

    /**
     * @param string $roleName
     * @param \App\Models\Clinic|null $clinic
     * @return bool
     */
    protected function passesForNewUser(string $roleName, Clinic $clinic = null): bool
    {
        switch ($roleName) {
            case Role::COMMUNITY_WORKER:
                return $this->user->canMakeCommunityWorker($clinic);
            case Role::CLINIC_ADMIN:
                return $this->user->canMakeClinicAdmin($clinic);
            case Role::ORGANISATION_ADMIN:
                return $this->user->canMakeOrganisationAdmin();
        }
    }

    /**
     * @param string $roleName
     * @param \App\Models\Clinic|null $clinic
     * @return bool
     */
    protected function passesForExistingUser(string $roleName, Clinic $clinic = null): bool
    {
        switch ($roleName) {
            case Role::COMMUNITY_WORKER:
                $isAlreadyCommunityWorker = $this->subjectUser->isCommunityWorker($clinic);

                return $isAlreadyCommunityWorker ?: $this->user->canMakeCommunityWorker($clinic);
            case Role::CLINIC_ADMIN:
                $isAlreadyClinicAdmin = $this->subjectUser->isClinicAdmin($clinic);

                return $isAlreadyClinicAdmin ?: $this->user->canMakeClinicAdmin($clinic);
            case Role::ORGANISATION_ADMIN:
                $isAlreadyOrganisationAdmin = $this->subjectUser->isOrganisationAdmin();

                return $isAlreadyOrganisationAdmin ?: $this->user->canMakeOrganisationAdmin();
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'You are not authorised to add this role.';
    }
}

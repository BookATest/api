<?php

namespace App\Rules;

use App\Models\Clinic;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class CanRemoveRoles implements Rule
{
    /**
     * @var \App\Models\User
     */
    protected $requestingUser;

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
        $this->requestingUser = $requestingUser;
        $this->subjectUser = $subjectUser;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $roles
     * @return bool
     */
    public function passes($attribute, $roles)
    {
        if (!is_array($roles)) {
            return false;
        }

        foreach ($roles as $role) {
            if (!is_array($role)) {
                return false;
            }
        }

        try {
            // Filter down array to removed roles.
            $revokedRoles = $this->getRevokedRoles($roles);

            // Loop through each revoked role and check if the requesting user can remove it.
            foreach ($revokedRoles as $revokedRole) {
                if (!$this->requestingUser->canRevokeRole($this->subjectUser, $revokedRole)) {
                    return false;
                }
            }

            return true;
        } catch (Throwable $throwable) {
            return false;
        }
    }

    /**
     * @param array $roles
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getRevokedRoles(array $roles): Collection
    {
        $roles = UserRole::parseArray($roles);

        return $this->subjectUser->getRevokedRoles($roles);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'You are not authorised to remove these roles.';
    }
}

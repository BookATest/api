<?php

namespace App\Models;

use App\Models\Mutators\UserRoleMutators;
use App\Models\Relationships\UserRoleRelationships;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class UserRole extends Model
{
    use UserRoleMutators;
    use UserRoleRelationships;

    /**
     * @param \App\Models\Clinic|null $clinic
     * @return bool
     */
    public function isCommunityWorker(Clinic $clinic = null): bool
    {
        $isCommunityWorker = $this->role->name === Role::COMMUNITY_WORKER;
        $forClinic = $clinic ? ($this->clinic_id === $clinic->id) : true;

        return $isCommunityWorker && $forClinic;
    }

    /**
     * @param \App\Models\Clinic|null $clinic
     * @return bool
     */
    public function isClinicAdmin(Clinic $clinic = null): bool
    {
        $isClinicAdmin = $this->role->name === Role::CLINIC_ADMIN;
        $forClinic = $clinic ? ($this->clinic_id === $clinic->id) : true;

        return $isClinicAdmin && $forClinic;
    }

    /**
     * @return bool
     */
    public function isOrganisationAdmin(): bool
    {
        return $this->role->name === Role::ORGANISATION_ADMIN;
    }

    /**
     * @param array $userRoles
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function parseArray(array $userRoles): Collection
    {
        foreach ($userRoles as $userRole) {
            if (!is_array($userRoles)) {
                throw new InvalidArgumentException('The user roles must be a two dimensional array');
            }

            if (!isset($userRole['role'])) {
                throw new InvalidArgumentException('The [role] key must be present');
            }

            if (
                in_array($userRole['role'], [Role::COMMUNITY_WORKER, Role::CLINIC_ADMIN]) &&
                !isset($userRole['clinic_id'])
            ) {
                throw new InvalidArgumentException('The [clinic_id] key must be present');
            }
        }

        $collection = new Collection();
        array_walk($userRoles, function (array $userRole) use (&$collection) {
            switch ($userRole['role']) {
                case Role::COMMUNITY_WORKER:
                    $collection->push(new UserRole([
                        'role_id' => Role::communityWorker()->id,
                        'clinic_id' => $userRole['clinic_id'],
                    ]));
                    break;
                case Role::CLINIC_ADMIN:
                    $collection->push(new UserRole([
                        'role_id' => Role::clinicAdmin()->id,
                        'clinic_id' => $userRole['clinic_id'],
                    ]));
                    break;
                case Role::ORGANISATION_ADMIN:
                    $collection->push(new UserRole([
                        'role_id' => Role::organisationAdmin()->id,
                    ]));
                    break;
            }
        });

        return $collection;
    }
}

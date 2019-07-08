<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Mutators\RoleMutators;
use App\Models\Relationships\RoleRelationships;

class Role extends Model
{
    use RoleMutators;
    use RoleRelationships;

    const COMMUNITY_WORKER = 'community_worker';
    const CLINIC_ADMIN = 'clinic_admin';
    const ORGANISATION_ADMIN = 'organisation_admin';

    /**
     * TODO: Cache the response.
     *
     * @return \App\Models\Role
     */
    public static function communityWorker(): self
    {
        return static::where('name', static::COMMUNITY_WORKER)->firstOrFail();
    }

    /**
     * TODO: Cache the response.
     *
     * @return \App\Models\Role
     */
    public static function clinicAdmin(): self
    {
        return static::where('name', static::CLINIC_ADMIN)->firstOrFail();
    }

    /**
     * TODO: Cache the response.
     *
     * @return \App\Models\Role
     */
    public static function organisationAdmin(): self
    {
        return static::where('name', static::ORGANISATION_ADMIN)->firstOrFail();
    }
}

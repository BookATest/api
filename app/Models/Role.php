<?php

namespace App\Models;

use App\Models\Mutators\RoleMutators;
use App\Models\Relationships\RoleRelationships;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use RoleMutators;
    use RoleRelationships;

    const COMMUNITY_WORKER = 'community_worker';
    const CLINIC_ADMIN = 'clinic_admin';
    const ORGANISATION_ADMIN = 'organisation_admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];
}

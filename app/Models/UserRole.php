<?php

namespace App\Models;

use App\Models\Mutators\UserRoleMutators;
use App\Models\Relationships\UserRoleRelationships;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use UserRoleMutators;
    use UserRoleRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'role_id',
        'clinic_id',
    ];
}

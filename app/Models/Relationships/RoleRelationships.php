<?php

namespace App\Models\Relationships;

use App\Models\User;
use App\Models\UserRole;

trait RoleRelationships
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }
}

<?php

namespace App\Models;

use App\Models\Mutators\UserMutators;
use App\Models\Relationships\UserRelationships;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;
    use UserMutators;
    use UserRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'display_email',
        'display_phone',
        'include_calendar_attachment',
        'calendar_feed_token',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'disabled_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'display_phone' => 'boolean',
        'display_email' => 'boolean',
        'include_calendar_attachment' => 'boolean',
    ];

    /**
     * @param \App\Models\Role $role
     * @return bool
     */
    public function hasRole(Role $role): bool
    {
        return $this->roles()->where('roles.id', $role->id)->exists();
    }

    /**
     * @param \App\Models\Role $role
     * @param \App\Models\Clinic|null $clinic
     * @return \App\Models\User
     */
    public function assignRole(Role $role, Clinic $clinic = null): self
    {
        // Check if the user already has the role.
        if ($this->hasRole($role)) {
            return $this;
        }

        // Create the role.
        UserRole::create(array_filter([
            'user_id' => $this->id,
            'role_id' => $role->id,
            'clinic_id' => $clinic->id ?? null,
        ]));

        return $this;
    }

    /**
     * @return \App\Models\User
     */
    public function makeOrganisationAdmin(): self
    {
        $role = Role::where('name', Role::ORGANISATION_ADMIN)->firstOrFail();

        return $this->assignRole($role);
    }
}

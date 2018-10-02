<?php

namespace App\Models;

use App\Models\Mutators\UserMutators;
use App\Models\Relationships\UserRelationships;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use RuntimeException;

class User extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;
    use UserMutators;
    use UserRelationships;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'display_phone' => 'boolean',
        'display_email' => 'boolean',
        'include_calendar_attachment' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'disabled_at' => 'datetime',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = uuid();
            }
        });
    }

    /**
     * @param \App\Models\Role $role
     * @param \App\Models\Clinic|null $clinic
     * @return bool
     */
    protected function hasRole(Role $role, Clinic $clinic = null): bool
    {
        $query = $this->userRoles()->where('user_roles.role_id', $role->id);

        return $clinic
            ? $query->where('user_roles.clinic_id', $clinic->id)->exists()
            : $query->exists();
    }

    /**
     * @param \App\Models\Role $role
     * @param \App\Models\Clinic|null $clinic
     * @return \App\Models\User
     */
    protected function assignRole(Role $role, Clinic $clinic = null): self
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
     * @param \App\Models\Role $role
     * @param \App\Models\Clinic|null $clinic
     * @return \App\Models\User
     */
    protected function removeRoll(Role $role, Clinic $clinic = null): self
    {
        // Check if the user doesn't already have the role.
        if (!$this->hasRole($role, $clinic)) {
            return $this;
        }

        // Remove the role.
        $this
            ->userRoles()
            ->where('role_id', $role->id)
            ->when($clinic, function (Builder $query) use ($clinic) {
                return $query->where('clinic_id', $clinic->id);
            })
            ->delete();

        return $this;
    }

    /**
     * @param \App\Models\Clinic $clinic
     * @return bool
     */
    public function isCommunityWorker(Clinic $clinic): bool
    {
        return $this->hasRole(Role::communityWorker(), $clinic) || $this->hasRole(Role::organisationAdmin());
    }

    /**
     * @param \App\Models\Clinic $clinic
     * @return bool
     */
    public function isClinicAdmin(Clinic $clinic): bool
    {
        return $this->hasRole(Role::clinicAdmin(), $clinic) || $this->hasRole(Role::organisationAdmin());
    }

    /**
     * @return bool
     */
    public function isOrganisationAdmin(): bool
    {
        return $this->hasRole(Role::organisationAdmin());
    }

    /**
     * @param \App\Models\Clinic $clinic
     * @return \App\Models\User
     */
    public function makeCommunityWorker(Clinic $clinic): self
    {
        return $this->assignRole(Role::communityWorker(), $clinic);
    }

    /**
     * @param \App\Models\Clinic $clinic
     * @return \App\Models\User
     */
    public function makeClinicAdmin(Clinic $clinic): self
    {
        $this->assignRole(Role::communityWorker(), $clinic);
        $this->assignRole(Role::clinicAdmin(), $clinic);

        return $this;
    }

    /**
     * @return \App\Models\User
     */
    public function makeOrganisationAdmin(): self
    {
        Clinic::all()->each(function (Clinic $clinic) {
            $this->assignRole(Role::communityWorker(), $clinic);
            $this->assignRole(Role::clinicAdmin(), $clinic);
        });

        $this->assignRole(Role::organisationAdmin());

        return $this;
    }

    /**
     * @param \App\Models\Clinic $clinic
     * @return \App\Models\User
     * @throws \Exception
     */
    public function revokeCommunityWorker(Clinic $clinic): self
    {
        $clinicAdminRole = Role::clinicAdmin();

        if ($this->hasRole($clinicAdminRole, $clinic)) {
            throw new \Exception('Cannot revoke community worker role when user is a clinic admin');
        }

        return $this->removeRoll($clinicAdminRole, $clinic);
    }

    /**
     * @param \App\Models\Clinic $clinic
     * @return \App\Models\User
     * @throws \Exception
     */
    public function revokeClinicAdmin(Clinic $clinic): self
    {
        $this->removeRoll(Role::clinicAdmin(), $clinic);
        $this->removeRoll(Role::communityWorker(), $clinic);

        return $this;
    }

    /**
     * @return \App\Models\User
     * @throws \Exception
     */
    public function revokeOrganisationAdmin(): self
    {
        return $this->removeRoll(Role::organisationAdmin());
    }

    /**
     * @param int $attempts
     * @return \App\Models\User
     */
    public function generateCalendarFeedToken(int $attempts = 0): self
    {
        // Prevent infinite loop.
        if ($attempts > 10) {
            throw new RuntimeException('Failed generating calendar feed token');
        }

        // Generate the token.
        $token = str_random(10);

        // Use recursion if the token has already been used.
        if (static::where('calendar_feed_token', $token)->exists()) {
            $token = $this->generateCalendarFeedToken(++$attempts);
        }

        // Set the token on the model.
        $this->calendar_feed_token = $token;
        $this->save();

        return $this;
    }
}

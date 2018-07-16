<?php

namespace App\Models\Relationships;

use App\Models\Appointment;
use App\Models\AppointmentSchedule;
use App\Models\Audit;
use App\Models\File;
use App\Models\Report;
use App\Models\ReportSchedule;
use App\Models\Role;
use App\Models\UserRole;

trait UserRelationships
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function profilePictureFile()
    {
        return $this->belongsTo(File::class, 'profile_picture_file_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointmentSchedules()
    {
        return $this->hasMany(AppointmentSchedule::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reportSchedules()
    {
        return $this->hasMany(ReportSchedule::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function audits()
    {
        return $this->morphMany(Audit::class, 'auditable');
    }
}

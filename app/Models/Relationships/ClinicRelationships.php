<?php

declare(strict_types=1);

namespace App\Models\Relationships;

use App\Models\AnonymisedAnswer;
use App\Models\Appointment;
use App\Models\AppointmentSchedule;
use App\Models\EligibleAnswer;
use App\Models\Report;
use App\Models\ReportSchedule;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;

trait ClinicRelationships
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
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
    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function eligibleAnswers()
    {
        return $this->hasMany(EligibleAnswer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function anonymisedAnswers()
    {
        return $this->hasMany(AnonymisedAnswer::class);
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinicAdmins()
    {
        return $this->belongsToMany(User::class, (new UserRole())->getTable())
            ->wherePivot('role_id', '=', Role::clinicAdmin()->id);
    }
}

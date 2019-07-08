<?php

namespace App\Models\Relationships;

use App\Models\Answer;
use App\Models\AppointmentSchedule;
use App\Models\Clinic;
use App\Models\ServiceUser;
use App\Models\User;

trait AppointmentRelationships
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointmentSchedule()
    {
        return $this->belongsTo(AppointmentSchedule::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}

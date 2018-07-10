<?php

namespace App\Models;

use App\Models\Mutators\AppointmentScheduleMutators;
use App\Models\Relationships\AppointmentScheduleRelationships;
use Illuminate\Database\Eloquent\Model;

class AppointmentSchedule extends Model
{
    use AppointmentScheduleMutators;
    use AppointmentScheduleRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'clinic_id',
        'weekly_on',
        'weekly_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];
}

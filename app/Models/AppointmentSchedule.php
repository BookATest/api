<?php

namespace App\Models;

use App\Models\Mutators\AppointmentScheduleMutators;
use App\Models\Relationships\AppointmentScheduleRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppointmentSchedule extends Model
{
    use AppointmentScheduleMutators;
    use AppointmentScheduleRelationships;
    use SoftDeletes;

    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;
    const SUNDAY = 7;

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

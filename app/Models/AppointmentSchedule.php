<?php

namespace App\Models;

use App\Models\Relationships\AppointmentScheduleRelationships;
use Illuminate\Database\Eloquent\Model;

class AppointmentSchedule extends Model
{
    use AppointmentScheduleRelationships;
}

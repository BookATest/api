<?php

namespace App\Models;

use App\Models\Mutators\ReportScheduleMutators;
use App\Models\Relationships\ReportScheduleRelationships;

class ReportSchedule extends Model
{
    use ReportScheduleMutators;
    use ReportScheduleRelationships;

    const WEEKLY = 'weekly';
    const MONTHLY= 'monthly';
}

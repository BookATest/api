<?php

namespace App\Models\Relationships;

use App\Models\Report;
use App\Models\ReportSchedule;

trait ReportTypeRelationships
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
}

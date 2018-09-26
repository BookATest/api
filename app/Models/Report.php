<?php

namespace App\Models;

use App\Models\Mutators\ReportMutators;
use App\Models\Relationships\ReportRelationships;

class Report extends Model
{
    use ReportMutators;
    use ReportRelationships;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_at' => 'date',
        'end_at' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

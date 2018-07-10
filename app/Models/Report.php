<?php

namespace App\Models;

use App\Models\Relationships\ReportRelationships;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use ReportRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'file_id',
        'clinic_id',
        'report_type_id',
        'start_at',
        'end_at',
    ];
}

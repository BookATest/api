<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportType extends Model
{
    const COUNT_APPOINTMENTS_AVAILABLE = 'count_appointments_available';
    const COUNT_APPOINTMENTS_BOOKED = 'count_appointments_booked';
    const COUNT_DID_NOT_ATTEND = 'count_did_not_attend';
    const COUNT_TESTING_TYPES = 'count_testing_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];
}

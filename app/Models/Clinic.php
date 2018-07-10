<?php

namespace App\Models;

use App\Models\Mutators\ClinicMutators;
use App\Models\Relationships\ClinicRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clinic extends Model
{
    use ClinicMutators;
    use ClinicRelationships;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address_line_1',
        'address_line_2',
        'address_line_3',
        'city',
        'postcode',
        'directions',
        'appointment_duration',
        'appointment_booking_threshold',
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

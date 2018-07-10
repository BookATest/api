<?php

namespace App\Models;

use App\Models\Relationships\ClinicRelationships;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use ClinicRelationships;

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
}

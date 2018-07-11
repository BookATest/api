<?php

namespace App\Models;

use App\Models\Mutators\AppointmentMutators;
use App\Models\Relationships\AppointmentRelationships;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use AppointmentMutators;
    use AppointmentRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'clinic_id',
        'appointment_schedule_id',
        'start_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'start_at',
        'booked_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'did_not_attend' => 'boolean',
    ];

    /**
     * @return bool
     */
    public function isBooked(): bool
    {
        return $this->service_user_uuid !== null;
    }
}

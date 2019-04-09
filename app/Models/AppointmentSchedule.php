<?php

namespace App\Models;

use App\Models\Mutators\AppointmentScheduleMutators;
use App\Models\Relationships\AppointmentScheduleRelationships;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

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
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * @param int|null $daysToSkip
     * @param int $daysUpTo
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createAppointments(int $daysToSkip = 60, int $daysUpTo = 90): Collection
    {
        $appointments = new Collection();

        // Loop through the date range.
        foreach (range($daysToSkip, $daysUpTo) as $day) {
            // Get the date of the looped day in the future.
            $startAt = today()
                ->addDays($day)
                ->setTimeFromTimeString($this->weekly_at);

            // Skip the day if it does not fall on the repeat day of week.
            if ($startAt->dayOfWeekIso !== $this->weekly_on) {
                continue;
            }

            $appointmentExists = Appointment::query()
                ->where('user_id', $this->user_id)
                ->where('clinic_id', $this->clinic_id)
                ->where('start_at', $startAt)
                ->exists();

            // Don't create an appointment if one already exists.
            if ($appointmentExists) {
                continue;
            }

            // Create an appointment and append to the collection.
            $appointments->push(Appointment::create([
                'user_id' => $this->user_id,
                'clinic_id' => $this->clinic_id,
                'appointment_schedule_id' => $this->id,
                'start_at' => $startAt,
            ]));
        }

        return $appointments;
    }

    /**
     * @param \Illuminate\Support\Carbon $date
     */
    public function deleteFrom(Carbon $date)
    {
        $this->appointments()
            ->available()
            ->where('appointments.start_at', '>=', $date)
            ->get()
            ->each
            ->delete();

        $this->delete();
    }
}

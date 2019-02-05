<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

trait AppointmentScopes
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $available
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable(Builder $query, bool $available = true): Builder
    {
        return $available
            ? $query->whereNull('appointments.service_user_id')
            : $query->whereNotNull('appointments.service_user_id');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $booked
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBooked(Builder $query, bool $booked = true): Builder
    {
        return $booked
            ? $query->whereNotNull('appointments.service_user_id')
            : $query->whereNull('appointments.service_user_id');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFuture(Builder $query): Builder
    {
        return $query->where('appointments.start_at', '>', now());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('appointments.start_at', [
            today()->startOfWeek(),
            today()->endOfWeek(),
        ]);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $dateTime
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStartsAfter(Builder $query, $dateTime)
    {
        $dateTime = $dateTime instanceof Carbon
            ? $dateTime
            : Carbon::createFromFormat(Carbon::ATOM, $dateTime);

        return $query->where('appointments.start_at', '>=', $dateTime);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $dateTime
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStartsBefore(Builder $query, $dateTime)
    {
        $dateTime = $dateTime instanceof Carbon
            ? $dateTime
            : Carbon::createFromFormat(Carbon::ATOM, $dateTime);

        return $query->where('appointments.start_at', '<=', $dateTime);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $minutes
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFinishedXMinutesAgo(Builder $query, int $minutes)
    {
        $sql = <<< EOT
DATE_ADD(
    DATE_ADD(
        `appointments`.`start_at`, 
        INTERVAL (
            SELECT `clinics`.`appointment_duration` 
            FROM `clinics` 
            WHERE `clinics`.`id` = `appointments`.`clinic_id`
        ) MINUTE
    ), 
    INTERVAL $minutes MINUTE
)
EOT;

        return $query->where(DB::raw($sql), '=', now()->second(0));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDnaUnactioned(Builder $query)
    {
        return $query->whereNull('appointments.did_not_attend');
    }
}

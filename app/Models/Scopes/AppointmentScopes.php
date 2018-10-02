<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

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
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBooked(Builder $query): Builder
    {
        return $query->whereNotNull('appointments.service_user_id');
    }
}

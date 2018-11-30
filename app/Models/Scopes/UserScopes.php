<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait UserScopes
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $clinicId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClinicId(Builder $query, string $clinicId): Builder
    {
        return $query->whereHas('clinics', function (Builder $query) use ($clinicId) {
            $query->where('clinics.id', '=', $clinicId);
        });
    }
}

<?php

declare(strict_types=1);

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

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $disabled
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisabled(Builder $query, bool $disabled): Builder
    {
        return $disabled
            ? $query->whereNotNull('disabled_at')
            : $query->whereNull('disabled_at');
    }
}

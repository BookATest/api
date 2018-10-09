<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait EligibleAnswerScopes
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereHas('question');
    }
}

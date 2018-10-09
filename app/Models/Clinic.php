<?php

namespace App\Models;

use App\Models\Mutators\ClinicMutators;
use App\Models\Relationships\ClinicRelationships;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clinic extends Model
{
    use ClinicMutators;
    use ClinicRelationships;
    use SoftDeletes;

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
     * @return bool
     */
    public function hasEligibleAnswers(): bool
    {
        return $this->eligibleAnswers()->current()->exists();
    }
}

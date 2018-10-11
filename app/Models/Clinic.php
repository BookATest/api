<?php

namespace App\Models;

use App\Models\Mutators\ClinicMutators;
use App\Models\Relationships\ClinicRelationships;
use App\Support\Coordinate;
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
        $nonTextQuestionsExist = Question::query()
            ->where('type', '!=', Question::TEXT)
            ->exists();

        return $nonTextQuestionsExist
            ? $this->eligibleAnswers()->current()->exists()
            : true;
    }

    /**
     * @param \App\Support\Coordinate $coordinate
     * @return \App\Models\Clinic
     */
    public function setCoordinate(Coordinate $coordinate): self
    {
        $this->lat = $coordinate->getLatitude();
        $this->lon = $coordinate->getLongitude();

        return $this;
    }
}

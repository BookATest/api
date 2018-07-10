<?php

namespace App\Models;

use App\Models\Mutators\EligibleAnswerMutators;
use App\Models\Relationships\EligibleAnswerRelationships;
use Illuminate\Database\Eloquent\Model;

class EligibleAnswer extends Model
{
    use EligibleAnswerMutators;
    use EligibleAnswerRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'clinic_id',
        'question_id',
        'answer',
    ];
}

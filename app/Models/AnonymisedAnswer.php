<?php

namespace App\Models;

use App\Models\Mutators\AnonymisedAnswerMutators;
use App\Models\Relationships\AnonymisedAnswerRelationships;
use Illuminate\Database\Eloquent\Model;

class AnonymisedAnswer extends Model
{
    use AnonymisedAnswerMutators;
    use AnonymisedAnswerRelationships;

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

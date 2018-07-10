<?php

namespace App\Models;

use App\Models\Relationships\AnonymisedAnswerRelationships;
use Illuminate\Database\Eloquent\Model;

class AnonymisedAnswer extends Model
{
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

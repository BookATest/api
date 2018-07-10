<?php

namespace App\Models;

use App\Models\Relationships\QuestionOptionRelationships;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use QuestionOptionRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'question_id',
        'option',
    ];
}

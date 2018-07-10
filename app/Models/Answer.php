<?php

namespace App\Models;

use App\Models\Relationships\AnswerRelationships;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use AnswerRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_user_uuid',
        'appointment_id',
        'question_id',
        'answer',
    ];
}

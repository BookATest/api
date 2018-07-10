<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnonymisedAnswer extends Model
{
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

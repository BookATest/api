<?php

namespace App\Models;

use App\Models\Mutators\QuestionMutators;
use App\Models\Relationships\QuestionRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use QuestionMutators;
    use QuestionRelationships;
    use SoftDeletes;

    const SELECT = 'select';
    const CHECKBOX = 'checkbox';
    const DATE = 'date';
    const TEXT = 'text';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'question',
        'type',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];
}

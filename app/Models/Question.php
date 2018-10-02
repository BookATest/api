<?php

namespace App\Models;

use App\Models\Mutators\QuestionMutators;
use App\Models\Relationships\QuestionRelationships;
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
     * @return string[]
     */
    public function getAvailableOptions(): array
    {
        return $this->questionOptions
            ->map(function (QuestionOption $questionOption) {
                return $questionOption->option;
            })
            ->toArray();
    }
}

<?php

namespace App\Models\Relationships;

use App\Models\Question;

trait QuestionOptionRelationships
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}

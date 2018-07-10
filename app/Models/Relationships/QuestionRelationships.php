<?php

namespace App\Models\Relationships;

use App\Models\AnonymisedAnswer;
use App\Models\Answer;
use App\Models\EligibleAnswer;
use App\Models\QuestionOption;

trait QuestionRelationships
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function anonymisedAnswers()
    {
        return $this->hasMany(AnonymisedAnswer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function eligibleAnswers()
    {
        return $this->hasMany(EligibleAnswer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questionOptions()
    {
        return $this->hasMany(QuestionOption::class);
    }
}

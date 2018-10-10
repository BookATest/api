<?php

namespace App\Models\Mutators;

trait EligibleAnswerMutators
{
    /*
     * Answer.
     */

    public function getAnswerAttribute(string $answer)
    {
        return json_decode($answer, true);
    }

    public function setAnswerAttribute($answer)
    {
        $this->attributes['answer'] = json_encode($answer);
    }
}

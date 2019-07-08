<?php

declare(strict_types=1);

namespace App\Models\Mutators;

trait AnswerMutators
{
    /*
     * Answer.
     */

    public function getAnswerAttribute(string $answer)
    {
        return json_decode(decrypt($answer), true);
    }

    public function setAnswerAttribute($answer)
    {
        $this->attributes['answer'] = encrypt(json_encode($answer));
    }
}

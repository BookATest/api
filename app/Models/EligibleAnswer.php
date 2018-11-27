<?php

namespace App\Models;

use App\Models\Mutators\EligibleAnswerMutators;
use App\Models\Relationships\EligibleAnswerRelationships;
use App\Models\Scopes\EligibleAnswerScopes;
use InvalidArgumentException;

class EligibleAnswer extends Model
{
    use EligibleAnswerMutators;
    use EligibleAnswerRelationships;
    use EligibleAnswerScopes;

    /**
     * Used to prepare the data to insert as the answer field JSON.
     *
     * @param array $answer
     * @param \App\Models\Question $question
     * @return array
     */
    public static function parseSelectAnswer(array $answer, Question $question): array
    {
        foreach ($answer as $value) {
            if (!is_string($value)) {
                throw new InvalidArgumentException('The answer must be an array of strings');
            }
        }

        $options = $question->questionOptions()->pluck('option')->toArray();

        foreach ($answer as $option) {
            if (!in_array($option, $options)) {
                throw new InvalidArgumentException('The answer must be a provided option for the question');
            }
        }

        return $answer;
    }

    /**
     * Used to prepare the data to insert as the answer field JSON.
     *
     * @param bool|null $answer
     * @return bool|null
     */
    public static function parseCheckboxAnswer(?bool $answer): ?bool
    {
        return $answer;
    }

    /**
     * Used to prepare the data to insert as the answer field JSON.
     *
     * @param array $answer
     * @return array
     */
    public static function parseDateAnswer(array $answer): array
    {
        if (!isset($answer['comparison']) || !isset($answer['interval'])) {
            throw new InvalidArgumentException('The "comparison" and "interval" keys must be present');
        }

        if (!is_string($answer['comparison'])) {
            throw new InvalidArgumentException('The "comparison" is invalid');
        }

        if (!in_array($answer['comparison'], ['<', '>'])) {
            throw new InvalidArgumentException('The "comparison" is invalid');
        }

        if (!is_int($answer['interval'])) {
            throw new InvalidArgumentException('The "interval" is invalid');
        }

        if ($answer['interval'] < 0) {
            throw new InvalidArgumentException('The "interval" is invalid');
        }

        return [
            'comparison' => $answer['comparison'],
            'interval' => $answer['interval'],
        ];
    }
}

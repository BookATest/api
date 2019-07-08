<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Question;
use Illuminate\Contracts\Validation\Rule;

class AllAnswersPresent implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $answers
     * @return bool
     */
    public function passes($attribute, $answers)
    {
        // Validation.
        if (!is_array($answers)) {
            return false;
        }

        foreach ($answers as $answer) {
            if (!is_array($answer)) {
                return false;
            }

            if (!isset($answer['question_id'])) {
                return false;
            }
        }

        // Get an array of both the submitted and current question IDs.
        $submittedQuestionIds = array_map(function (array $answer) {
            return $answer['question_id'];
        }, $answers);

        $currentQuestionIds = Question::query()
            ->where('type', '!=', Question::TEXT)
            ->pluck('id')
            ->toArray();

        // Loop through each of the current questions IDs and make sure the user has provided an answer.
        foreach ($currentQuestionIds as $questionId) {
            if (!in_array($questionId, $submittedQuestionIds)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'You must provide eligible answers for all questions.';
    }
}

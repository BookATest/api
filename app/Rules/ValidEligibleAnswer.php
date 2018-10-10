<?php

namespace App\Rules;

use App\Models\Question;
use Illuminate\Contracts\Validation\Rule;

class ValidEligibleAnswer implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $answer
     * @return bool
     */
    public function passes($attribute, $answer)
    {
        if (!is_array($answer)) {
            return false;
        }

        if (!isset($answer['question_id']) || !isset($answer['answer'])) {
            return false;
        }

        if (!is_string($answer['question_id'])) {
            return false;
        }

        $question = Question::find($answer['question_id']);

        if ($question === null) {
            return false;
        }

        switch($question->type) {
            case Question::SELECT:
                return $this->selectPasses($answer['answer'], $question);
            case Question::DATE:
                return $this->datePasses($answer['answer']);
            case Question::CHECKBOX:
                return $this->checkboxPasses($answer['answer']);
            case Question::TEXT:
            default:
                return false;
        }
    }

    /**
     * Ensures that the answer is an array of strings.
     *
     * @param mixed $answer
     * @param \App\Models\Question $question
     * @return bool
     */
    protected function selectPasses($answer, Question $question): bool
    {
        if (!is_array($answer)) {
            return false;
        }

        foreach ($answer as $option) {
            if (!is_string($option)) {
                return false;
            }
        }

        $options = $question->questionOptions()->pluck('option')->toArray();

        foreach ($answer as $option) {
            if (!in_array($option, $options)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Ensures that the answer is a valid date comparison object.
     *
     * @param mixed $answer
     * @return bool
     */
    protected function datePasses($answer): bool
    {
        if (!is_array($answer)) {
            return false;
        }

        if (!isset($answer['comparison']) || !isset($answer['interval'])) {
            return false;
        }

        if (!is_string($answer['comparison'])) {
            return false;
        }

        if (!in_array($answer['comparison'], ['<', '>'])) {
            return false;
        }

        if (!is_int($answer['interval'])) {
            return false;
        }

        if ($answer['interval'] < 0) {
            return false;
        }

        return true;
    }

    /**
     * Ensures that the answer is a boolean.
     *
     * @param mixed $answer
     * @return bool
     */
    protected function checkboxPasses($answer): bool
    {
        return is_bool($answer);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'This is not a valid answer.';
    }
}

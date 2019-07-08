<?php

namespace App\Rules;

use App\Models\Question;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\Rule;
use InvalidArgumentException;

class ValidAnswer implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $answer
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

        switch ($question->type) {
            case Question::SELECT:
                return $this->selectPasses($answer['answer'], $question);
            case Question::DATE:
                return $this->datePasses($answer['answer']);
            case Question::CHECKBOX:
                return $this->checkboxPasses($answer['answer']);
            case Question::TEXT:
                return $this->textPasses($answer['answer']);
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
        if (!is_string($answer)) {
            return false;
        }

        $options = $question->questionOptions()->pluck('option')->toArray();

        return in_array($answer, $options);
    }

    /**
     * Ensures that the answer is a valid date time string.
     *
     * @param mixed $answer
     * @return bool
     */
    protected function datePasses($answer): bool
    {
        if (!is_string($answer)) {
            return false;
        }

        try {
            CarbonImmutable::createFromFormat('Y-m-d', $answer);
        } catch (InvalidArgumentException $exception) {
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
     * Ensures that the answer is a string.
     *
     * @param mixed $answer
     * @return bool
     */
    protected function textPasses($answer): bool
    {
        return is_string($answer);
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

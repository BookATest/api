<?php

namespace App\Rules;

use App\Models\Appointment;
use App\Models\EligibleAnswer;
use App\Models\Question;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;

class ValidAnswerForAppointment implements Rule
{
    /**
     * @var \App\Models\Appointment|null
     */
    protected $appointment;

    /**
     * ValidAnswer constructor.
     *
     * @param \App\Models\Appointment|null $appointment
     */
    public function __construct(?Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $answer
     * @return bool
     */
    public function passes($attribute, $answer)
    {
        if (!$this->appointment instanceof Appointment) {
            return false;
        }

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

        $eligibleAnswer = EligibleAnswer::query()
            ->where('clinic_id', $this->appointment->clinic_id)
            ->where('question_id', $question->id)
            ->first();

        if ($eligibleAnswer === null && $question->type !== Question::TEXT) {
            return false;
        }

        switch ($question->type) {
            case Question::SELECT:
                return $this->selectPasses($answer['answer'], $eligibleAnswer);
            case Question::DATE:
                return $this->datePasses($answer['answer'], $eligibleAnswer);
            case Question::CHECKBOX:
                return $this->checkboxPasses($answer['answer'], $eligibleAnswer);
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
     * @param \App\Models\EligibleAnswer $eligibleAnswer
     * @return bool
     */
    protected function selectPasses($answer, EligibleAnswer $eligibleAnswer): bool
    {
        if (!is_string($answer)) {
            return false;
        }

        return in_array($answer, $eligibleAnswer->answer);
    }

    /**
     * Ensures that the answer is a valid date time string.
     *
     * @param mixed $answer
     * @param \App\Models\EligibleAnswer $eligibleAnswer
     * @return bool
     */
    protected function datePasses($answer, EligibleAnswer $eligibleAnswer): bool
    {
        if (!is_string($answer)) {
            return false;
        }

        try {
            $answer = CarbonImmutable::createFromFormat('Y-m-d', $answer);
        } catch (InvalidArgumentException $exception) {
            return false;
        }

        switch ($eligibleAnswer->answer['comparison']) {
            case '>':
                return Date::now()->diffInSeconds($answer) >= $eligibleAnswer->answer['interval'];
            case '<':
                return Date::now()->diffInSeconds($answer) <= $eligibleAnswer->answer['interval'];
        }
    }

    /**
     * Ensures that the answer is a boolean.
     *
     * @param mixed $answer
     * @param \App\Models\EligibleAnswer $eligibleAnswer
     * @return bool
     */
    protected function checkboxPasses($answer, EligibleAnswer $eligibleAnswer): bool
    {
        if (!is_bool($answer)) {
            return false;
        }

        if ($eligibleAnswer->answer === null) {
            return true;
        }

        return $answer === $eligibleAnswer->answer;
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

<?php

namespace App\Models;

use App\Models\Mutators\AppointmentMutators;
use App\Models\Relationships\AppointmentRelationships;
use App\Models\Scopes\AppointmentScopes;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class Appointment extends Model
{
    use AppointmentMutators;
    use AppointmentRelationships;
    use AppointmentScopes;

    const ATTENDED = false;
    const DID_NOT_ATTEND = true;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'did_not_attend' => 'boolean',
        'start_at' => 'datetime',
        'booked_at' => 'datetime',
        'consented_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * @return bool
     */
    public function hasSchedule(): bool
    {
        return $this->appointment_schedule_id !== null;
    }

    /**
     * @param \App\Models\ServiceUser $serviceUser
     * @param \Illuminate\Support\Carbon|null $bookedAt
     * @return \App\Models\Appointment
     */
    public function book(ServiceUser $serviceUser, Carbon $bookedAt = null): self
    {
        $bookedAt = $bookedAt ?? now();

        $this->update([
            'service_user_id' => $serviceUser->id,
            'booked_at' => $bookedAt,
            'consented_at' => $bookedAt,
        ]);

        return $this;
    }

    /**
     * @param bool $didNotAttend
     * @return \App\Models\Appointment
     */
    public function setDnaStatus(bool $didNotAttend): self
    {
        $this->update([
            'did_not_attend' => $didNotAttend,
        ]);

        return $this;
    }

    /**
     * @return \App\Models\Appointment
     */
    public function cancel(): self
    {
        $this->answers()->delete();

        $this->update([
            'service_user_id' => null,
            'booked_at' => null,
            'consented_at' => null,
        ]);

        return $this;
    }

    /**
     * @param \App\Models\Question $question
     * @param \App\Models\ServiceUser $serviceUser
     * @param string $answer
     * @return \App\Models\Answer
     */
    public function createSelectAnswer(Question $question, ServiceUser $serviceUser, string $answer): Answer
    {
        // Validation.
        $questionType = Question::SELECT;

        if ($question->type !== $questionType) {
            throw new InvalidArgumentException("The question must be of type [{$questionType}]");
        }

        $options = $question->questionOptions()->pluck('option')->toArray();

        if (!in_array($answer, $options)) {
            throw new InvalidArgumentException('The answer must be a provided option for the question');
        }

        // Create an anonymised answer.
        AnonymisedAnswer::create([
            'clinic_id' => $this->clinic_id,
            'question_id' => $question->id,
            'answer' => $answer,
        ]);

        // Create the answer.
        $answer = $this->answers()->create([
            'service_user_id' => $serviceUser->id,
            'question_id' => $question->id,
            'answer' => $answer,
        ]);

        return $answer;
    }

    /**
     * @param \App\Models\Question $question
     * @param \App\Models\ServiceUser $serviceUser
     * @param bool $answer
     * @return \App\Models\Answer
     */
    public function createCheckboxAnswer(Question $question, ServiceUser $serviceUser, bool $answer): Answer
    {
        // Validation.
        $questionType = Question::CHECKBOX;

        if ($question->type !== $questionType) {
            throw new InvalidArgumentException("The question must be of type [{$questionType}]");
        }

        // Create an anonymised answer.
        AnonymisedAnswer::create([
            'clinic_id' => $this->clinic_id,
            'question_id' => $question->id,
            'answer' => $answer,
        ]);

        // Create the answer.
        $answer = $this->answers()->create([
            'service_user_id' => $serviceUser->id,
            'question_id' => $question->id,
            'answer' => $answer,
        ]);

        return $answer;
    }

    /**
     * @param \App\Models\Question $question
     * @param \App\Models\ServiceUser $serviceUser
     * @param \Illuminate\Support\Carbon $dateTime
     * @return \App\Models\Answer
     */
    public function createDateAnswer(Question $question, ServiceUser $serviceUser, Carbon $dateTime): Answer
    {
        // Validation.
        $questionType = Question::DATE;

        if ($question->type !== $questionType) {
            throw new InvalidArgumentException("The question must be of type [{$questionType}]");
        }

        // Create an anonymised answer.
        AnonymisedAnswer::create([
            'clinic_id' => $this->clinic_id,
            'question_id' => $question->id,
            'answer' => $dateTime->toDateTimeString(),
        ]);

        // Create the answer.
        $answer = $this->answers()->create([
            'service_user_id' => $serviceUser->id,
            'question_id' => $question->id,
            'answer' => $dateTime->toDateTimeString(),
        ]);

        return $answer;
    }

    /**
     * @param \App\Models\Question $question
     * @param \App\Models\ServiceUser $serviceUser
     * @param string $answer
     * @return \App\Models\Answer
     */
    public function createTextAnswer(Question $question, ServiceUser $serviceUser, string $answer): Answer
    {
        // Validation.
        $questionType = Question::TEXT;

        if ($question->type !== $questionType) {
            throw new InvalidArgumentException("The question must be of type [{$questionType}]");
        }

        // Create an anonymised answer.
        AnonymisedAnswer::create([
            'clinic_id' => $this->clinic_id,
            'question_id' => $question->id,
            'answer' => $answer,
        ]);

        // Create the answer.
        $answer = $this->answers()->create([
            'service_user_id' => $serviceUser->id,
            'question_id' => $question->id,
            'answer' => $answer,
        ]);

        return $answer;
    }
}

<?php

namespace App\Models;

use App\Models\Mutators\ClinicMutators;
use App\Models\Relationships\ClinicRelationships;
use App\Support\Coordinate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;

class Clinic extends Model
{
    use ClinicMutators;
    use ClinicRelationships;
    use SoftDeletes;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'send_cancellation_confirmations' => 'boolean',
        'send_dna_follow_ups' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Called just after the model is created.
     *
     * @param \App\Models\Model $model
     */
    protected function onCreated(Model $model)
    {
        Role::organisationAdmin()->users->each->makeClinicAdmin($model);
    }

    /**
     * Called just before the model is deleted.
     */
    protected function onDeleting()
    {
        // Cancel all booked appointments in the future.
        $this->appointments()
            ->booked()
            ->where('start_at', '>', Date::now()->timezone('UTC'))
            ->chunk(200, function (Collection $appointments) {
                $appointments->each->cancel();
            });

        // Delete all appointment schedules.
        $this->appointmentSchedules()->get()->each->delete();

        // Delete all unbooked appointments.
        $this->appointments()->available()->get()->each->delete();
    }

    /**
     * @return bool
     */
    public function hasEligibleAnswers(): bool
    {
        $nonTextQuestionsExist = Question::query()
            ->where('type', '!=', Question::TEXT)
            ->exists();

        return $nonTextQuestionsExist
            ? $this->eligibleAnswers()->current()->exists()
            : true;
    }

    /**
     * @param \App\Support\Coordinate $coordinate
     * @return \App\Models\Clinic
     */
    public function setCoordinate(Coordinate $coordinate): self
    {
        $this->lat = $coordinate->getLatitude();
        $this->lon = $coordinate->getLongitude();

        return $this;
    }

    /**
     * @param array $answers
     * @return bool
     */
    public function isEligible(array $answers): bool
    {
        if (!$this->hasEligibleAnswers()) {
            return false;
        }

        foreach ($answers as $answer) {
            $question = Question::findOrFail($answer['question_id']);

            $eligibleAnswer = $this->eligibleAnswers()
                ->where('question_id', $question->id)
                ->first();

            switch ($question->type) {
                case Question::SELECT:
                    if (!$this->selectIsEligible($answer['answer'], $eligibleAnswer)) {
                        return false;
                    }
                    continue 2;
                case Question::DATE:
                    if (!$this->dateIsEligible($answer['answer'], $eligibleAnswer)) {
                        return false;
                    }
                    continue 2;
                case Question::CHECKBOX:
                    if (!$this->checkboxIsEligible($answer['answer'], $eligibleAnswer)) {
                        return false;
                    }
                    continue 2;
                case Question::TEXT:
                    continue 2;
                default:
                    return false;
            }
        }

        return true;
    }

    /**
     * @param string $answer
     * @param \App\Models\EligibleAnswer $eligibleAnswer
     * @return bool
     */
    protected function selectIsEligible(string $answer, EligibleAnswer $eligibleAnswer): bool
    {
        return in_array($answer, $eligibleAnswer->answer);
    }

    /**
     * @param string $answer
     * @param \App\Models\EligibleAnswer $eligibleAnswer
     * @return bool
     */
    protected function dateIsEligible(string $answer, EligibleAnswer $eligibleAnswer): bool
    {
        try {
            $answer = Date::createFromFormat('Y-m-d', $answer);
        } catch (InvalidArgumentException $exception) {
            return false;
        }

        switch ($eligibleAnswer->answer['comparison']) {
            case '>':
                return Date::now()->diffInSeconds($answer) >= $eligibleAnswer->answer['interval'];
            case '<':
                return Date::now()->diffInSeconds($answer) <= $eligibleAnswer->answer['interval'];
        }

        return false;
    }

    /**
     * @param bool $answer
     * @param \App\Models\EligibleAnswer $eligibleAnswer
     * @return bool
     */
    protected function checkboxIsEligible(bool $answer, EligibleAnswer $eligibleAnswer): bool
    {
        // Null indicates either answer is eligible.
        if ($eligibleAnswer->answer === null) {
            return true;
        }

        return $answer === $eligibleAnswer->answer;
    }

    /**
     * @return \App\Support\Coordinate|null
     */
    public function coordinate(): ?Coordinate
    {
        if ($this->lat === null && $this->lon === null) {
            return null;
        }

        return new Coordinate($this->lat, $this->lon);
    }
}

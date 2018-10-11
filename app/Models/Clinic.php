<?php

namespace App\Models;

use App\Models\Mutators\ClinicMutators;
use App\Models\Relationships\ClinicRelationships;
use App\Support\Coordinate;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

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
        foreach ($answers as $answer) {
            $eligibleAnswer = $this->eligibleAnswers()
                ->with('question')
                ->where('question_id', $answer['question_id'])
                ->firstOrFail();

            switch ($eligibleAnswer->question->type) {
                case Question::SELECT:
                    if (!$this->selectIsEligible($answer, $eligibleAnswer->answer)) {
                        return false;
                    }
                    continue;
                case Question::DATE:
                    if (!$this->dateIsEligible($answer, $eligibleAnswer->answer)) {
                        return false;
                    }
                    continue;
                case Question::CHECKBOX:
                    if (!$this->checkboxIsEligible($answer, $eligibleAnswer->answer)) {
                        return false;
                    }
                    continue;
                case Question::TEXT:
                    continue;
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
            $answer = Carbon::createFromFormat(Carbon::ATOM, $answer);
        } catch (InvalidArgumentException $exception) {
            return false;
        }

        switch ($eligibleAnswer->answer['comparison']) {
            case '>':
                return now()->diffInSeconds($answer) >= $eligibleAnswer->answer['interval'];
            case '<':
                return now()->diffInSeconds($answer) <= $eligibleAnswer->answer['interval'];
        }

        return false;
    }

    /**
     * @param string $answer
     * @param \App\Models\EligibleAnswer $eligibleAnswer
     * @return bool
     */
    protected function checkboxIsEligible(string $answer, EligibleAnswer $eligibleAnswer): bool
    {
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

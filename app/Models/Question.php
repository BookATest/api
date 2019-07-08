<?php

namespace App\Models;

use App\Models\Mutators\QuestionMutators;
use App\Models\Relationships\QuestionRelationships;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use QuestionMutators;
    use QuestionRelationships;
    use SoftDeletes;

    const SELECT = 'select';
    const CHECKBOX = 'checkbox';
    const DATE = 'date';
    const TEXT = 'text';

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
     * Soft deletes all the questions.
     */
    public static function invalidateAll()
    {
        static::query()->delete();
    }

    /**
     * @param string $question
     * @param string[] $options
     * @return \App\Models\Question
     */
    public static function createSelect(string $question, string ...$options): self
    {
        $question = static::create([
            'question' => $question,
            'type' => static::SELECT,
        ]);

        foreach ($options as $option) {
            $question->questionOptions()->create([
                'option' => $option,
            ]);
        }

        return $question;
    }

    /**
     * @param string $question
     * @return \App\Models\Question
     */
    public static function createCheckbox(string $question): self
    {
        return static::create([
            'question' => $question,
            'type' => static::CHECKBOX,
        ]);
    }

    /**
     * @param string $question
     * @return \App\Models\Question
     */
    public static function createDate(string $question): self
    {
        return static::create([
            'question' => $question,
            'type' => static::DATE,
        ]);
    }

    /**
     * @param string $question
     * @return \App\Models\Question
     */
    public static function createText(string $question): self
    {
        return static::create([
            'question' => $question,
            'type' => static::TEXT,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DurationFitsIntoDay implements Rule
{
    const MINUTES_IN_DAY = 1440;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!is_int($value)) {
            return false;
        }

        return static::MINUTES_IN_DAY % $value === 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a duration that perfectly fits into a single day.';
    }
}

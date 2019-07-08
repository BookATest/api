<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class CalendarFeedTokenIsValid implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $token
     * @return bool
     */
    public function passes($attribute, $token)
    {
        if (!is_string($token)) {
            return false;
        }

        return User::findByCalendarFeedToken($token) !== null;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is invalid.';
    }
}

<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UkPhoneNumber implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $phoneNumber
     * @return bool
     */
    public function passes($attribute, $phoneNumber)
    {
        // Immediately fail if the value is not a string.
        if (!is_string($phoneNumber)) {
            return false;
        }

        $matches = preg_match('/^(0[0-9]{10})$/', $phoneNumber);

        return $matches === 1;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid UK phone number.';
    }
}

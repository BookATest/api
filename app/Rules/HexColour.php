<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class HexColour implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $hex
     * @return bool
     */
    public function passes($attribute, $hex)
    {
        if (!is_string($hex)) {
            return false;
        }

        return (bool)preg_match('/^#[a-zA-Z0-9]{6}$/', $hex);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid hex colour.';
    }
}

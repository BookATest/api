<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Base64EncodedPng implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $image
     * @return bool
     */
    public function passes($attribute, $image)
    {
        // Immediately fail if the value is not a string.
        if (!is_string($image)) {
            return false;
        }

        return (bool)preg_match('/^(data:image\/png;base64,)/', $image);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute field must be a base 64 encoded string of a PNG image.';
    }
}

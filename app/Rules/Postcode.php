<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Postcode implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $postcode
     * @return bool
     */
    public function passes($attribute, $postcode)
    {
        if (!is_string($postcode)) {
            return false;
        }

        return \App\Support\Postcode::validate($postcode);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The postcode is invalid.';
    }
}

<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Password implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $password
     * @return bool
     */
    public function passes($attribute, $password)
    {
        // Immediately fail if the value is not a string.
        if (!is_string($password)) {
            return false;
        }

        return (bool)preg_match(
            '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&.\(\)])[A-Za-z\d$@$!%*?&.\(\)]{8,}/',
            $password
        );
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be at least eight characters long, 
            contain one uppercase letter, 
            one lowercase letter, 
            one number and one special character ($@$!%*?&.).';
    }
}

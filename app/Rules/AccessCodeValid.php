<?php

namespace App\Rules;

use App\Models\ServiceUser;
use Illuminate\Contracts\Validation\Rule;

class AccessCodeValid implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $accessCode
     * @return bool
     */
    public function passes($attribute, $accessCode)
    {
        return ServiceUser::validateAccessCode($accessCode);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is not valid.';
    }
}

<?php

namespace App\Rules;

use App\Models\ServiceUser;
use Illuminate\Contracts\Validation\Rule;

class AccessCodeValid implements Rule
{
    /**
     * @var string
     */
    protected $phone;

    /**
     * AccessCodeValid constructor.
     *
     * @param string $phone
     */
    public function __construct($phone)
    {
        $this->phone = $phone;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $accessCode
     * @return bool
     */
    public function passes($attribute, $accessCode)
    {
        // Fail if the phone is not a string.
        if (!is_string($this->phone)) {
            return false;
        }

        return ServiceUser::validateAccessCode($accessCode, $this->phone);
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

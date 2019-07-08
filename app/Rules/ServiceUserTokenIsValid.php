<?php

namespace App\Rules;

use App\Models\ServiceUser;
use Illuminate\Contracts\Validation\Rule;

class ServiceUserTokenIsValid implements Rule
{
    /**
     * @var \App\Models\ServiceUser
     */
    protected $serviceUser;

    /**
     * Create a new rule instance.
     *
     * @param \App\Models\ServiceUser $serviceUser
     */
    public function __construct(ServiceUser $serviceUser)
    {
        $this->serviceUser = $serviceUser;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $serviceUserToken
     * @return bool
     */
    public function passes($attribute, $serviceUserToken)
    {
        if (!is_string($serviceUserToken)) {
            return false;
        }

        if (!ServiceUser::validateToken($serviceUserToken)) {
            return false;
        }

        return ServiceUser::findByToken($serviceUserToken)->id === $this->serviceUser->id;
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

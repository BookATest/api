<?php

namespace App\Rules;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class IsCommunityWorkerForClinic implements Rule
{
    /**
     * @var \App\Models\User
     */
    protected $user;

    /**
     * @var \App\Models\Clinic|null
     */
    protected $clinic;

    /**
     * Create a new rule instance.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Clinic|null $clinic
     */
    public function __construct(User $user, ?Clinic $clinic)
    {
        $this->user = $user;
        $this->clinic = $clinic;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->clinic === null) {
            return false;
        }

        return $this->user->isCommunityWorker($this->clinic);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'You must be a community worker for this clinic.';
    }
}

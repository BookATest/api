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
     * Create a new rule instance.
     *
     * @param \App\Models\User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $clinicId
     * @return bool
     */
    public function passes($attribute, $clinicId)
    {
        if (!is_string($clinicId)) {
            return false;
        }

        $clinic = Clinic::find($clinicId);

        if ($clinic === null) {
            return false;
        }

        return $this->user->isCommunityWorker($clinic);
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

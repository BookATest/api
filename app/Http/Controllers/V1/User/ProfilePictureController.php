<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Models\User;

class ProfilePictureController extends Controller
{
    /**
     * @param \App\Models\User $user
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @return \Illuminate\Http\Response|mixed
     */
    public function __invoke(User $user)
    {
        return $user->profilePictureFile ?? $user->placeholderProfilePicture();
    }
}

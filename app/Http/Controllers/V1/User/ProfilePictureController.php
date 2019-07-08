<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Models\User;

class ProfilePictureController extends Controller
{
    /**
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response|mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function __invoke(User $user)
    {
        return $user->profilePictureFile ?? $user->placeholderProfilePicture();
    }
}

<?php

namespace App\Http\Controllers\V1\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class LogoController extends Controller
{
    /**
     * @return \App\Models\File|\Illuminate\Http\Response|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function __invoke()
    {
        return Setting::logoFile() ?? Setting::placeholderLogoPicture();
    }
}

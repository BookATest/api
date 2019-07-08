<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class LogoController extends Controller
{
    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @return \App\Models\File|\Illuminate\Http\Response|null
     */
    public function __invoke()
    {
        return Setting::logoFile() ?? Setting::placeholderLogoPicture();
    }
}

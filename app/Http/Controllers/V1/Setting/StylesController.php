<?php

namespace App\Http\Controllers\V1\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Response;

class StylesController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        return response()->make(
            Setting::getValue(Setting::STYLES),
            Response::HTTP_OK,
            ['Content-Type' => 'text/css']
        );
    }
}

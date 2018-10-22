<?php

namespace App\Http\Controllers;

class ApiController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function v1()
    {
        return response()->json([
            'version' => 'v1.0.1-alpha',
            'base_path' => url('/v1'),
        ]);
    }
}

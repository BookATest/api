<?php

namespace App\Http\Controllers;

use App\Docs\OpenApi;

class DocsController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('docs');
    }

    /**
     * @param \App\Docs\OpenApi $openApi
     * @return \Illuminate\Http\Response
     */
    public function openapi(OpenApi $openApi)
    {
        return response()->json($openApi->generate());
    }
}

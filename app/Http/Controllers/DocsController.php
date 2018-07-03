<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class DocsController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('docs.index');
    }

    /**
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function openapi()
    {
        $yaml = view('docs.openapi')->render();

        return response()->make($yaml, Response::HTTP_OK, [
            'Content-Type' => 'application/x-yaml',
            'Content-Disposition' => 'inline; filename="openapi.yaml"',
        ]);
    }
}

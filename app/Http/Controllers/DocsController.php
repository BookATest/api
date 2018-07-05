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
     * @param string $path
     *
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function openapi(string $path)
    {
        $path = str_replace('.yaml', '', $path);
        $path = str_replace('/', '.', $path);
        $path = 'docs.' . $path;

        $yaml = view($path)->render();

        return response()->make($yaml, Response::HTTP_OK, [
            'Content-Type' => 'application/x-yaml',
            'Content-Disposition' => 'inline; filename="openapi.yaml"',
        ]);
    }
}

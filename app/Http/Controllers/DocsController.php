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
        // Parse the URL path so Laravel can find the corresponding view.
        $path = str_replace('.json', '', $path);
        $view = str_replace('/', '.', $path);
        $view = 'docs.' . $view;
        $pathParts = explode('/', $path);
        $filename = array_last($pathParts);

        // 404 if the view does not exist.
        if (!view()->exists($view)) {
            abort(Response::HTTP_NOT_FOUND);
        }

        // Parse the YAML Blade template and retrieve the string contents.
        $json = view($view)->render();

        return response()->make($json, Response::HTTP_OK, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => sprintf('inline; filename="%s.json"', $filename),
        ]);
    }
}

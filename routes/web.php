<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::get('/docs', function () {
    return view('docs.index');
});

Route::get('/docs/openapi.yaml', function () {
    $yaml = view('docs.openapi')->render();

    return response()->make($yaml, Response::HTTP_OK, [
        'Content-Type' => 'application/x-yaml',
        'Content-Disposition' => 'inline; filename="openapi.yaml"',
    ]);
});

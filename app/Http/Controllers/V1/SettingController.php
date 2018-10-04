<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\{IndexRequest, UpdateRequest};
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Http\Requests\Setting\IndexRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexRequest $request)
    {
        $settings = Setting::all()->mapWithKeys(function (Setting $setting) {
            return [$setting->key => $setting->value];
        });

        event(EndpointHit::onRead($request, "Viewed settings"));

        return response()->json(['data' => $settings]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request)
    {
        
    }
}

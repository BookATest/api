<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\IndexRequest;
use App\Http\Requests\Setting\UpdateRequest;
use App\Models\File;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * SettingController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api')->except('index');
    }

    /**
     * Display a listing of the resource.
     *
     * @param \App\Http\Requests\Setting\IndexRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexRequest $request)
    {
        $settings = Setting::getAll();
        unset($settings[Setting::LOGO_FILE_ID]);

        event(EndpointHit::onRead($request, "Viewed settings"));

        return response()->json(['data' => $settings]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Setting\UpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request)
    {
        DB::transaction(function () use ($request) {
            Setting::findOrFail('default_appointment_booking_threshold')
                ->update(['value' => (int)$request->default_appointment_booking_threshold]);

            Setting::findOrFail('default_appointment_duration')
                ->update(['value' => (int)$request->default_appointment_duration]);

            Setting::findOrFail('language')->update(['value' => [
                'booking_questions_help_text' => (string)$request->language['booking_questions_help_text'],
                'booking_notification_help_text' => (string)$request->language['booking_notification_help_text'],
                'booking_enter_details_help_text' => (string)$request->language['booking_enter_details_help_text'],
                'booking_find_location_help_text' => (string)$request->language['booking_find_location_help_text'],
                'booking_appointment_overview_help_text' => (string)$request->language['booking_appointment_overview_help_text'],
            ]]);

            Setting::findOrFail('name')
                ->update(['value' => (string)$request->name]);

            Setting::findOrFail('primary_colour')
                ->update(['value' => (string)$request->primary_colour]);

            Setting::findOrFail('secondary_colour')
                ->update(['value' => (string)$request->secondary_colour]);

            if ($request->has('logo')) {
                $file = File::create([
                    'filename' => 'organisation-logo.png',
                    'mime_type' => File::MIME_PNG,
                ]);

                Setting::findOrFail('logo_file_id')
                    ->update(['value' => $file->id]);

                $file->uploadBase64EncodedImage($request->logo);
            }
        });

        $settings = Setting::getAll();
        unset($settings[Setting::LOGO_FILE_ID]);

        event(EndpointHit::onUpdate($request, "Updated settings"));

        return response()->json(['data' => $settings]);
    }
}

<?php

declare(strict_types=1);

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
        $this->middleware('throttle:60,1');
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

        event(EndpointHit::onRead($request, 'Viewed settings'));

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
                'home' => [
                    'title' => (string)$request->language['home']['title'],
                    'content' => (string)$request->language['home']['content'] ?: null,
                ],
                'make-booking' => [
                    'introduction' => [
                        'title' => (string)$request->language['make-booking']['introduction']['title'],
                        'content' => (string)$request->language['make-booking']['introduction']['content'] ?: null,
                    ],
                    'questions' => [
                        'title' => (string)$request->language['make-booking']['questions']['title'],
                        'content' => (string)$request->language['make-booking']['questions']['content'] ?: null,
                    ],
                    'location' => [
                        'title' => (string)$request->language['make-booking']['location']['title'],
                        'content' => (string)$request->language['make-booking']['location']['content'] ?: null,
                    ],
                    'clinics' => [
                        'title' => (string)$request->language['make-booking']['clinics']['title'],
                        'content' => (string)$request->language['make-booking']['clinics']['content'] ?: null,
                        'ineligible' => (string)$request->language['make-booking']['clinics']['ineligible'],
                    ],
                    'appointments' => [
                        'title' => (string)$request->language['make-booking']['appointments']['title'],
                        'content' => (string)$request->language['make-booking']['appointments']['content'] ?: null,
                    ],
                    'user-details' => [
                        'title' => (string)$request->language['make-booking']['user-details']['title'],
                        'content' => (string)$request->language['make-booking']['user-details']['content'] ?: null,
                    ],
                    'consent' => [
                        'title' => (string)$request->language['make-booking']['consent']['title'],
                        'content' => (string)$request->language['make-booking']['consent']['content'] ?: null,
                    ],
                    'no-consent' => [
                        'title' => (string)$request->language['make-booking']['no-consent']['title'],
                        'content' => (string)$request->language['make-booking']['no-consent']['content'] ?: null,
                    ],
                    'overview' => [
                        'title' => (string)$request->language['make-booking']['overview']['title'],
                        'content' => (string)$request->language['make-booking']['overview']['content'] ?: null,
                    ],
                    'confirmation' => [
                        'title' => (string)$request->language['make-booking']['confirmation']['title'],
                        'content' => (string)$request->language['make-booking']['confirmation']['content'] ?: null,
                    ],
                ],
                'list-bookings' => [
                    'access-code' => [
                        'title' => (string)$request->language['list-bookings']['access-code']['title'],
                        'content' => (string)$request->language['list-bookings']['access-code']['content'] ?: null,
                    ],
                    'token' => [
                        'title' => (string)$request->language['list-bookings']['token']['title'],
                        'content' => (string)$request->language['list-bookings']['token']['content'] ?: null,
                    ],
                    'appointments' => [
                        'title' => (string)$request->language['list-bookings']['appointments']['title'],
                        'content' => (string)$request->language['list-bookings']['appointments']['content'] ?: null,
                        'disclaimer' => (string)$request->language['list-bookings']['appointments']['disclaimer'],
                    ],
                    'cancel' => [
                        'title' => (string)$request->language['list-bookings']['cancel']['title'],
                        'content' => (string)$request->language['list-bookings']['cancel']['content'] ?: null,
                    ],
                    'cancelled' => [
                        'title' => (string)$request->language['list-bookings']['cancelled']['title'],
                        'content' => (string)$request->language['list-bookings']['cancelled']['content'] ?: null,
                    ],
                    'token-expired' => [
                        'title' => (string)$request->language['list-bookings']['token-expired']['title'],
                        'content' => (string)$request->language['list-bookings']['token-expired']['content'] ?: null,
                    ],
                ],
            ]]);

            Setting::findOrFail('name')
                ->update(['value' => (string)$request->name]);

            Setting::findOrFail('email')
                ->update(['value' => (string)$request->email]);

            Setting::findOrFail('phone')
                ->update(['value' => (string)$request->phone]);

            Setting::findOrFail('primary_colour')
                ->update(['value' => (string)$request->primary_colour]);

            Setting::findOrFail('secondary_colour')
                ->update(['value' => (string)$request->secondary_colour]);

            Setting::findOrFail('styles')
                ->update(['value' => (string)$request->styles]);

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

        event(EndpointHit::onUpdate($request, 'Updated settings'));

        return response()->json(['data' => $settings]);
    }
}

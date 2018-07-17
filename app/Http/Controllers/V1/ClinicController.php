<?php

namespace App\Http\Controllers\V1;

use App\Events\EndpointHit;
use App\Http\Requests\Clinic\StoreRequest;
use App\Http\Resources\ClinicResource;
use App\Models\Clinic;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ClinicController extends Controller
{
    /**
     * ClinicController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api')->only('store', 'update', 'destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Clinic\StoreRequest $request
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function store(StoreRequest $request)
    {
        event(EndpointHit::onCreate($request, 'Created clinic'));

        return DB::transaction(function () use ($request) {
            $clinic = Clinic::create([
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'address_line_1' => $request->input('address_line_1'),
                'address_line_2' => $request->input('address_line_2'),
                'address_line_3' => $request->input('address_line_3'),
                'city' => $request->input('city'),
                'postcode' => $request->input('postcode'),
                'directions' => $request->input('directions'),
                'appointment_duration' => $request->input('appointment_duration', Setting::getValue(Setting::DEFAULT_APPOINTMENT_DURATION)),
                'appointment_booking_threshold' => $request->input('appointment_booking_threshold', Setting::getValue(Setting::DEFAULT_APPOINTMENT_BOOKING_THRESHOLD)),
            ]);

            return new ClinicResource($clinic);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Clinic  $clinic
     * @return \Illuminate\Http\Response
     */
    public function show(Clinic $clinic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Clinic  $clinic
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Clinic $clinic)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Clinic  $clinic
     * @return \Illuminate\Http\Response
     */
    public function destroy(Clinic $clinic)
    {
        //
    }
}

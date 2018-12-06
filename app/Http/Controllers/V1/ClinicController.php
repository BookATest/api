<?php

namespace App\Http\Controllers\V1;

use App\Contracts\Geocoder;
use App\Events\EndpointHit;
use App\Http\Requests\Clinic\DestroyRequest;
use App\Http\Requests\Clinic\IndexRequest;
use App\Http\Requests\Clinic\ShowRequest;
use App\Http\Requests\Clinic\StoreRequest;
use App\Http\Requests\Clinic\UpdateRequest;
use App\Http\Resources\ClinicResource;
use App\Http\Responses\ResourceDeletedResponse;
use App\Models\Clinic;
use App\Http\Controllers\Controller;
use App\Support\Postcode;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\Filter;
use Spatie\QueryBuilder\QueryBuilder;

class ClinicController extends Controller
{
    /**
     * @var \App\Contracts\Geocoder
     */
    protected $geocoder;

    /**
     * ClinicController constructor.
     *
     * @param \App\Contracts\Geocoder $geocoder
     */
    public function __construct(Geocoder $geocoder)
    {
        $this->middleware('auth:api')->except('index', 'show');

        $this->geocoder = $geocoder;
    }

    /**
     * Display a listing of the resource.
     *
     * @param \App\Http\Requests\Clinic\IndexRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(IndexRequest $request)
    {
        // Prepare the base query.
        $baseQuery = Clinic::query();

        // Specify allowed modifications to the query via the GET parameters.
        $clinics = QueryBuilder::for($baseQuery)
            ->defaultSort('name')
            ->allowedSorts('name')
            ->allowedFilters(
                Filter::exact('id')
            )
            ->paginate(per_page($request->per_page));

        event(EndpointHit::onRead($request, 'Listed all clinics'));

        return ClinicResource::collection($clinics);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Clinic\StoreRequest $request
     * @return \App\Http\Resources\ClinicResource
     */
    public function store(StoreRequest $request)
    {
        $clinic = DB::transaction(function () use ($request) {
            $clinic = new Clinic([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address_line_1' => $request->address_line_1,
                'address_line_2' => $request->address_line_2,
                'address_line_3' => $request->address_line_3,
                'city' => $request->city,
                'postcode' => strtoupper($request->postcode),
                'directions' => $request->directions,
                'appointment_duration' => $request->appointment_duration,
                'appointment_booking_threshold' => $request->appointment_booking_threshold,
                'send_cancellation_confirmations' => $request->send_cancellation_confirmations,
            ]);

            $coordinate = $this->geocoder->geocode(
                new Postcode($request->postcode)
            );
            $clinic->setCoordinate($coordinate)->save();

            return $clinic;
        });

        event(EndpointHit::onCreate($request, "Created clinic [{$clinic->id}]"));

        return new ClinicResource($clinic);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Http\Requests\Clinic\ShowRequest $request
     * @param  \App\Models\Clinic $clinic
     * @return \App\Http\Resources\ClinicResource
     */
    public function show(ShowRequest $request, Clinic $clinic)
    {
        event(EndpointHit::onRead($request, "Viewed clinic [{$clinic->id}]"));

        return new ClinicResource($clinic);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Clinic\UpdateRequest $request
     * @param  \App\Models\Clinic $clinic
     * @return \App\Http\Resources\ClinicResource
     */
    public function update(UpdateRequest $request, Clinic $clinic)
    {
        $clinic = DB::transaction(function () use ($request, $clinic) {
            $clinic->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address_line_1' => $request->address_line_1,
                'address_line_2' => $request->address_line_2,
                'address_line_3' => $request->address_line_3,
                'city' => $request->city,
                'postcode' => strtoupper($request->postcode),
                'directions' => $request->directions,
                // TODO: 'appointment_duration' => $request->appointment_duration,
                'appointment_booking_threshold' => $request->appointment_booking_threshold,
                'send_cancellation_confirmations' => $request->send_cancellation_confirmations,
            ]);

            $coordinate = $this->geocoder->geocode(
                new Postcode($request->postcode)
            );
            $clinic->setCoordinate($coordinate)->save();

            return $clinic;
        });

        event(EndpointHit::onUpdate($request, "Updated clinic [{$clinic->id}]"));

        return new ClinicResource($clinic);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Http\Requests\Clinic\DestroyRequest $request
     * @param  \App\Models\Clinic $clinic
     * @return \App\Http\Responses\ResourceDeletedResponse
     */
    public function destroy(DestroyRequest $request, Clinic $clinic)
    {
        $clinicId = $clinic->id;

        DB::transaction(function () use ($clinic) {
            $clinic->delete();
        });

        event(EndpointHit::onDelete($request, "Deleted clinic [{$clinicId}]"));

        return new ResourceDeletedResponse(Clinic::class);
    }
}

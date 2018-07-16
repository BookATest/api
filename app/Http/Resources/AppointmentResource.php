<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'clinic_id' => $this->clinic_id,
            'is_repeating' => $this->appointment_schedule_id !== null,
            'service_user_uuid' => $this->service_user_uuid,
            'start_at' => $this->start_at->format(Carbon::ISO8601),
            'booked_at' => optional($this->booked_at)->format(Carbon::ISO8601),
            'did_not_attend' => $this->did_not_attend,
        ];
    }
}

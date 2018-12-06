<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClinicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'address_line_3' => $this->address_line_3,
            'city' => $this->city,
            'postcode' => $this->postcode,
            'directions' => $this->directions,
            'appointment_duration' => $this->appointment_duration,
            'appointment_booking_threshold' => $this->appointment_booking_threshold,
            'send_cancellation_confirmations' => $this->when($request->user(), $this->send_cancellation_confirmations),
            'send_dna_follow_ups' => $this->when($request->user(), $this->send_dna_follow_ups),
            'distance' => $this->when($this->hasAppend('distance'), $this->distance),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

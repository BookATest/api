<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
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
            'user_id' => $this->user_id,
            'clinic_id' => $this->clinic_id,
            'is_repeating' => $this->appointment_schedule_id !== null,
            'service_user_id' => $this->service_user_id,
            'start_at' => $this->start_at->toIso8601String(),
            'booked_at' => optional($this->booked_at)->toIso8601String(),
            'consented_at' => optional($this->consented_at)->toIso8601String(),
            'did_not_attend' => $this->did_not_attend,
            'service_user_name' => $this->when(
                $this->hasAppend('service_user_name') && $request->user('api'),
                $this->service_user_name
            ),
            'user_first_name' => $this->when($this->hasAppend('user_first_name'), $this->user_first_name),
            'user_last_name' => $this->when($this->hasAppend('user_last_name'), $this->user_last_name),
            'user_email' => $this->when($this->hasAppend('user_email'), $this->user_email),
            'user_phone' => $this->when($this->hasAppend('user_phone'), $this->user_phone),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

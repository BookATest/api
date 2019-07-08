<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'display_email' => $this->display_email,
            'display_phone' => $this->display_phone,
            'receive_booking_confirmations' => $this->receive_booking_confirmations,
            'receive_cancellation_confirmations' => $this->receive_cancellation_confirmations,
            'include_calendar_attachment' => $this->include_calendar_attachment,
            'calendar_feed_token' => $this->when(
                $request->user('api')->id === $this->id,
                $this->calendar_feed_token
            ),
            'roles' => RoleResource::collection($this->userRoles->load('role')),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

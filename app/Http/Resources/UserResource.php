<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'display_email' => $this->display_email,
            'display_phone' => $this->display_phone,
            'include_calendar_attachment' => $this->include_calendar_attachment,
            'roles' => RoleResource::collection($this->userRoles->load('role')),
            'created_at' => $this->created_at->format(Carbon::ISO8601),
            'updated_at' => $this->updated_at->format(Carbon::ISO8601),
        ];
    }
}

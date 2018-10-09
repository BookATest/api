<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class EligibleAnswerResource extends JsonResource
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
            'question_id' => $this->question_id,
            'answer' => $this->answer,
            'created_at' => $this->created_at->format(Carbon::ISO8601),
            'updated_at' => $this->updated_at->format(Carbon::ISO8601),
        ];
    }
}

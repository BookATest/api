<?php

namespace App\Events;

use App\Models\Audit;
use Illuminate\Http\Request;

class AppointmentEvent extends EndpointHitEvent
{
    /**
     * Create a new event instance.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $action
     * @param string $description
     */
    protected function __construct(Request $request, string $action, string $description)
    {
        $this->auditable = null;
        $this->client = optional($request->user()->token())->client;
        $this->action = $action;
        $this->description = $description;
        $this->ipAddress = $request->ip();
        $this->userAgent = $request->userAgent();
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \App\Events\AppointmentEvent
     */
    public static function onIndex(Request $request): self
    {
        return new static($request, Audit::READ, 'Viewed all appointments');
    }
}

<?php

namespace App\Listeners;

use App\Events\EndpointHit;
use App\Models\Audit;
use Illuminate\Events\Dispatcher;

class EndpointHitSubscriber
{
    /**
     * @param \App\Events\EndpointHit $event
     */
    public function onHit(EndpointHit $event)
    {
        // Filter out any null values.
        $attributes = array_filter([
            'client_id' => optional($event->getClient())->id,
            'action' => $event->getAction(),
            'description' => $event->getDescription(),
            'ip_address' => $event->getIpAddress(),
            'user_agent' => $event->getUserAgent(),
        ]);

        // When an authenticated user makes the request.
        if ($event->getAuditable()) {
            $event->getAuditable()->audits()->create($attributes);

            return;
        }

        // When a guest makes the request.
        Audit::create($attributes);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        // Appointment events.
        $events->listen(EndpointHit::class, static::class . '@onHit');
    }
}

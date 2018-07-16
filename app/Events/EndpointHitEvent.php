<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Laravel\Passport\Client;

abstract class EndpointHitEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @var null|\Illuminate\Database\Eloquent\Model
     */
    protected $auditable;

    /**
     * @var null|\Laravel\Passport\Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var null|string
     */
    protected $description;

    /**
     * @var string
     */
    protected $ipAddress;

    /**
     * @var string
     */
    protected $userAgent;

    /**
     * @return null|\Illuminate\Database\Eloquent\Model
     */
    public function getAuditable(): ?Model
    {
        return $this->auditable;
    }

    /**
     * @return null|\Laravel\Passport\Client
     */
    public function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }
}

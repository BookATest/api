<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Audit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Laravel\Passport\Client;

class EndpointHit
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @var \Illuminate\Database\Eloquent\Model|null
     */
    protected $auditable;

    /**
     * @var \Laravel\Passport\Client|null
     */
    protected $client;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var string|null
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
     * Create a new event instance.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $action
     * @param string $description
     */
    protected function __construct(Request $request, string $action, string $description)
    {
        $user = $request->user('api');
        $token = optional($user)->token();

        $this->auditable = $user;
        $this->client = optional($token)->client;
        $this->action = $action;
        $this->description = $description;
        $this->ipAddress = $request->ip();
        $this->userAgent = $request->userAgent();
    }

    /**
     * @param string $action
     * @param \Illuminate\Http\Request $request
     * @param string $description
     * @return \App\Events\EndpointHit
     */
    public static function onAction(string $action, Request $request, string $description): self
    {
        return new static($request, $action, $description);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param string $description
     * @return \App\Events\EndpointHit
     */
    public static function onCreate(Request $request, string $description): self
    {
        return static::onAction(Audit::CREATE, $request, $description);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param string $description
     * @return \App\Events\EndpointHit
     */
    public static function onRead(Request $request, string $description): self
    {
        return static::onAction(Audit::READ, $request, $description);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param string $description
     * @return \App\Events\EndpointHit
     */
    public static function onUpdate(Request $request, string $description): self
    {
        return static::onAction(Audit::UPDATE, $request, $description);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param string $description
     * @return \App\Events\EndpointHit
     */
    public static function onDelete(Request $request, string $description): self
    {
        return static::onAction(Audit::DELETE, $request, $description);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param string $description
     * @return \App\Events\EndpointHit
     */
    public static function onLogin(Request $request, string $description): self
    {
        return static::onAction(Audit::LOGIN, $request, $description);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param string $description
     * @return \App\Events\EndpointHit
     */
    public static function onLogout(Request $request, string $description): self
    {
        return static::onAction(Audit::LOGOUT, $request, $description);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getAuditable(): ?Model
    {
        return $this->auditable;
    }

    /**
     * @return \Laravel\Passport\Client|null
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
     * @return string|null
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

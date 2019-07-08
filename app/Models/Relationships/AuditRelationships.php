<?php

declare(strict_types=1);

namespace App\Models\Relationships;

use Laravel\Passport\Client;

trait AuditRelationships
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function auditable()
    {
        return $this->morphTo('auditable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}

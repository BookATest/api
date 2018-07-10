<?php

namespace App\Models\Relationships;

trait AuditRelationships
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function auditable()
    {
        return $this->morphTo('auditable');
    }
}

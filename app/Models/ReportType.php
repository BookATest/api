<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Mutators\ReportTypeMutators;
use App\Models\Relationships\ReportTypeRelationships;

class ReportType extends Model
{
    use ReportTypeMutators;
    use ReportTypeRelationships;

    const GENERAL_EXPORT = 'general_export';

    /**
     * @param string $name
     * @return \App\Models\ReportType
     */
    public static function findByName(string $name): self
    {
        return static::query()->where('name', $name)->firstOrFail();
    }
}

<?php

declare(strict_types=1);

namespace App\Docs\Resources;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class AuditResource extends BaseResource
{
    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema
     */
    public static function resource(): Schema
    {
        return Schema::object()->properties(
            Schema::string('id')->format(Schema::UUID),
            Schema::string('auditable_id')->format(Schema::UUID)->nullable(),
            Schema::string('auditable_type')->nullable(),
            Schema::string('client')->nullable(),
            Schema::string('action'),
            Schema::string('description')->nullable(),
            Schema::string('ip_address'),
            Schema::string('user_agent'),
            Schema::string('created_at')->format('date-time'),
            Schema::string('updated_at')->format('date-time')
        );
    }
}

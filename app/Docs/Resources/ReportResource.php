<?php

declare(strict_types=1);

namespace App\Docs\Resources;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class ReportResource extends BaseResource
{
    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema
     */
    public static function resource(): Schema
    {
        return Schema::object()->properties(
            Schema::string('id')->format(Schema::UUID),
            Schema::string('user_id')->format(Schema::UUID),
            Schema::string('clinic_id')->format(Schema::UUID)->nullable(),
            Schema::string('type')->enum('general_export'),
            Schema::string('start_at')->format('date'),
            Schema::string('end_at')->format('date'),
            Schema::string('created_at')->format('date-time'),
            Schema::string('updated_at')->format('date-time')
        );
    }
}

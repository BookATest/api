<?php

namespace App\Docs\Resources;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class StatResource extends BaseResource
{
    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema
     */
    public static function resource(): Schema
    {
        return Schema::object()->properties(
            Schema::integer('total_appointments'),
            Schema::integer('appointments_available'),
            Schema::integer('appointments_booked'),
            Schema::number('attendance_rate'),
            Schema::number('did_not_attend_rate'),
            Schema::string('start_at')->format(Schema::DATE),
            Schema::string('end_at')->format(Schema::DATE)
        );
    }
}

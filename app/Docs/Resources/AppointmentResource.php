<?php

declare(strict_types=1);

namespace App\Docs\Resources;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class AppointmentResource extends BaseResource
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
            Schema::boolean('is_repeating'),
            Schema::string('service_user_id')->format(Schema::UUID),
            Schema::string('start_at')->format('date-time'),
            Schema::string('booked_at')->format('date-time'),
            Schema::string('consented_at')->format('date-time'),
            Schema::boolean('did_not_attend'),
            Schema::string('created_at')->format('date-time'),
            Schema::string('updated_at')->format('date-time')
        );
    }
}

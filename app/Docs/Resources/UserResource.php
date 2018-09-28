<?php

namespace App\Docs\Resources;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class UserResource extends BaseResource
{
    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema
     */
    public static function resource(): Schema
    {
        return Schema::object()->properties(
            Schema::string('id')->format(Schema::UUID),
            Schema::string('first_name'),
            Schema::string('last_name'),
            Schema::string('email'),
            Schema::string('phone'),
            Schema::boolean('display_email'),
            Schema::boolean('display_phone'),
            Schema::boolean('include_calendar_attachment'),
            Schema::array('roles')->items(
                Schema::object()->properties(
                    Schema::string('role'),
                    Schema::string('clinic_id')->format(Schema::UUID)->nullable()
                )
            ),
            Schema::string('created_at')->format('date-time'),
            Schema::string('updated_at')->format('date-time')
        );
    }
}

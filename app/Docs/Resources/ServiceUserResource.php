<?php

namespace App\Docs\Resources;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class ServiceUserResource extends BaseResource
{
    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema
     */
    public static function resource(): Schema
    {
        return Schema::object()->properties(
            Schema::string('id')->format(Schema::UUID),
            Schema::string('name'),
            Schema::string('phone'),
            Schema::string('email'),
            Schema::string('preferred_contact_method')->enum('phone', 'email', 'both'),
            Schema::string('created_at')->format('date-time'),
            Schema::string('updated_at')->format('date-time')
        );
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema
     */
    public static function showWithAppointments(): Schema
    {
        $properties = static::resource()->properties;
        $properties[] = Schema::array('appointments')->items(AppointmentResource::resource());

        return Schema::object()
            ->properties(static::resource()->properties(...$properties)->name('data'));
    }
}

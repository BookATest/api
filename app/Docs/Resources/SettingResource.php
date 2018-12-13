<?php

namespace App\Docs\Resources;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SettingResource extends BaseResource
{
    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema
     */
    public static function resource(): Schema
    {
        return Schema::object()->properties(
            Schema::integer('default_appointment_booking_threshold'),
            Schema::integer('default_appointment_duration'),
            Schema::object('language')->properties(
                Schema::object('home')->properties(
                    Schema::string('title'),
                    Schema::string('content')
                ),
                Schema::object('make-booking')->properties(
                    Schema::object('clinics')->properties(
                        Schema::string('title'),
                        Schema::string('content')
                    ),
                    Schema::object('consent')->properties(
                        Schema::string('title'),
                        Schema::string('content')
                    ),
                    Schema::object('location')->properties(
                        Schema::string('title'),
                        Schema::string('content')
                    ),
                    Schema::object('overview')->properties(
                        Schema::string('title'),
                        Schema::string('content')
                    ),
                    Schema::object('questions')->properties(
                        Schema::string('title'),
                        Schema::string('content')
                    ),
                    Schema::object('appointments')->properties(
                        Schema::string('title'),
                        Schema::string('content')
                    ),
                    Schema::object('confirmation')->properties(
                        Schema::string('title'),
                        Schema::string('content')
                    ),
                    Schema::object('introduction')->properties(
                        Schema::string('title'),
                        Schema::string('content')
                    ),
                    Schema::object('user-details')->properties(
                        Schema::string('title'),
                        Schema::string('content')
                    )
                ),
                Schema::object('list-bookings')->properties(
                    Schema::object('token')->properties(
                        Schema::string('title'),
                        Schema::string('content')
                    ),
                    Schema::object('cancel')->properties(
                        Schema::string('title'),
                        Schema::string('content')
                    ),
                    Schema::object('cancelled')->properties(
                        Schema::string('title'),
                        Schema::string('content')
                    ),
                    Schema::object('access-code')->properties(
                        Schema::string('title'),
                        Schema::string('content')
                    ),
                    Schema::object('appointments')->properties(
                        Schema::string('title'),
                        Schema::string('content'),
                        Schema::string('disclaimer')
                    ),
                    Schema::object('token-expired')->properties(
                        Schema::string('title'),
                        Schema::string('content')
                    )
                )
            ),
            Schema::string('name'),
            Schema::string('email'),
            Schema::string('phone'),
            Schema::string('primary_colour'),
            Schema::string('secondary_colour')
        );
    }
}

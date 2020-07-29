<?php

namespace App\Docs\Resources;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class ClinicResource extends BaseResource
{
    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema
     */
    public static function resource(): Schema
    {
        return Schema::object()->properties(
            Schema::string('id')->format(Schema::FORMAT_UUID),
            Schema::string('phone'),
            Schema::string('name'),
            Schema::string('email'),
            Schema::string('address_line_1'),
            Schema::string('address_line_2')->nullable(),
            Schema::string('address_line_3')->nullable(),
            Schema::string('city'),
            Schema::string('postcode'),
            Schema::string('directions'),
            Schema::integer('appointment_duration'),
            Schema::integer('appointment_booking_threshold'),
            Schema::boolean('send_cancellation_confirmations')->description('Only shown for authenticated users'),
            Schema::boolean('send_dna_follow_ups')->description('Only shown for authenticated users'),
            Schema::object('language')->properties(
                Schema::object('make-booking')->properties(
                    Schema::object('appointments')->properties(
                        Schema::string('title')->nullable(),
                        Schema::string('content')->nullable()
                    )
                )
            ),
            Schema::string('created_at')->format('date-time'),
            Schema::string('updated_at')->format('date-time')
        );
    }
}

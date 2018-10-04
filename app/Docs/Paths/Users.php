<?php

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\UserResource;
use App\Docs\Responses;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class Users
{
    /**
     * Users constructor.
     */
    protected function __construct()
    {
        // Prevent instantiation.
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function index(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(UserResource::list())
            ),
        ];
        $parameters = [
            Parameter::query('filter[id]', Schema::string()->format(Schema::UUID))
                ->description('Comma separated user IDs'),
            Parameter::query('filter[clinic_id]', Schema::string()->format(Schema::UUID))
                ->description('Comma separated clinic IDs'),
            Parameter::query('sort', Schema::string()->default('first_name,last_name'))
                ->description('The field to sort the results by [`first_name`, `last_name`]'),
        ];

        return Operation::get(...$responses)
            ->parameters(...$parameters)
            ->summary('List all users')
            ->description('**Permission:** `Community Worker`')
            ->operationId('users.index')
            ->tags(Tags::users()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function store(): Operation
    {
        $responses = [
            Responses::http201(
                MediaType::json(UserResource::show())
            ),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required(
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'password',
                    'display_email',
                    'display_phone',
                    'include_calendar_attachment',
                    'roles'
                )
                ->properties(
                    Schema::string('first_name'),
                    Schema::string('last_name'),
                    Schema::string('email'),
                    Schema::string('phone'),
                    Schema::string('password')->format(Schema::PASSWORD),
                    Schema::boolean('display_email'),
                    Schema::boolean('include_calendar_attachment'),
                    Schema::array('roles')->items(Schema::object()
                        ->required('role')
                        ->properties(
                            Schema::string('role'),
                            Schema::string('clinic_id')->format(Schema::UUID)
                        )
                    ),
                    Schema::string('profile_picture')
                )
        );
        $description = <<<EOT
**Permission:** `Clinic Admin`
- Can create a user with the `Community Worker` role for a clinic that they are associated to

**Permission:** `Organisation Admin`
- Can create a user with any role for any clinic

***

Create a new users along with their roles
EOT;

        return Operation::post(...$responses)
            ->requestBody($requestBody)
            ->summary('Create a new user')
            ->description($description)
            ->operationId('users.store')
            ->tags(Tags::users()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function show(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(UserResource::show())
            ),
        ];
        $parameters = [
            Parameter::path('user', Schema::string()->format(Schema::UUID))
                ->description('The user ID')
                ->required(),
        ];

        return Operation::get(...$responses)
            ->parameters(...$parameters)
            ->summary('Get a specific user')
            ->description('**Permission:** `Community Worker`')
            ->operationId('users.show')
            ->tags(Tags::users()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function update(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(UserResource::show())
            ),
        ];
        $parameters = [
            Parameter::path('user', Schema::string()->format(Schema::UUID))
                ->description('The user ID')
                ->required(),
        ];
        $requestBody = Requests::json(
            Schema::object()
                ->required(
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'display_email',
                    'display_phone',
                    'include_calendar_attachment',
                    'roles'
                )
                ->properties(
                    Schema::string('first_name'),
                    Schema::string('last_name'),
                    Schema::string('email'),
                    Schema::string('phone'),
                    Schema::string('password')->format(Schema::PASSWORD),
                    Schema::boolean('display_email'),
                    Schema::boolean('include_calendar_attachment'),
                    Schema::array('roles')->items(Schema::object()
                        ->required('role')
                        ->properties(
                            Schema::string('role'),
                            Schema::string('clinic_id')->format(Schema::UUID)
                        )
                    ),
                    Schema::string('profile_picture')
                )
        );
        $description = <<<EOT
**Permission:** `Community Worker`
- Edit their own profile

**Permission:** `Clinic Admin`
- Edit their own profile
- Assign `Community Worker` role to other users at a clinic they are a `Clinic Admin` at
- Remove `Community Worker` from other users at a clinic they are a `Clinic Admin` at

**Permission:** `Organisation Admin`
- Edit all user profiles
- Assign any role to any user
- Remove any role from any user

***

Update a specific users along with their roles
EOT;

        return Operation::put(...$responses)
            ->parameters(...$parameters)
            ->requestBody($requestBody)
            ->summary('Update a specified user')
            ->description($description)
            ->operationId('users.update')
            ->tags(Tags::users()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function destroy(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(UserResource::deleted())
            ),
        ];
        $parameters = [
            Parameter::path('user', Schema::string()->format(Schema::UUID))
                ->description('The user ID')
                ->required(),
        ];

        $description = <<<EOT
**Permission:** `Organisation Admin`

***

This will only disable the user so they can no longer access the backend. All of this user's future appointments
will also be cancelled and deleted.
EOT;

        return Operation::delete(...$responses)
            ->parameters(...$parameters)
            ->summary('Delete a specific user')
            ->description($description)
            ->operationId('users.destroy')
            ->tags(Tags::users()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function profilePicture(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::create('image/png', Schema::string()->format(Schema::BINARY))
            ),
        ];
        $parameters = [
            Parameter::path('user', Schema::string()->format(Schema::UUID))
                ->description('The user ID')
                ->required(),
        ];

        return Operation::get(...$responses)
            ->parameters(...$parameters)
            ->summary('Get a specific user\'s profile picture')
            ->description('**Permission:** `Open`')
            ->operationId('users.profile-picture')
            ->tags(Tags::users()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function calendarFeedToken(): Operation
    {
        $responses = [
            Responses::http201(
                MediaType::json(Schema::object()->properties(
                    Schema::string('calendar_feed_token')
                ))
            ),
        ];
        $parameters = [
            Parameter::path('user', Schema::string()->format(Schema::UUID))
                ->description('The user ID')
                ->required(),
        ];
        $description = <<<EOT
**Permission:** `Community Worker`
- Can refresh their own calendar feed token
EOT;

        return Operation::put(...$responses)
            ->parameters(...$parameters)
            ->summary('Refresh the calendar feed token')
            ->description($description)
            ->operationId('users.calendar-feed-token')
            ->tags(Tags::users()->name);
    }
}

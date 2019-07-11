<?php

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\UserResource;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
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
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function index(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::json()->schema(UserResource::list())
                ),
        ];
        $parameters = [
            Parameter::query()
                ->name('filter[id]')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('Comma separated user IDs'),
            Parameter::query()
                ->name('filter[clinic_id]')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('Comma separated clinic IDs'),
            Parameter::query()
                ->name('filter[disabled]')
                ->schema(Schema::boolean())
                ->description('Filter users to only disabled or active, omit to show all users'),
            Parameter::query()
                ->name('sort')
                ->schema(Schema::string()->default('first_name,last_name'))
                ->description('The field to sort the results by [`first_name`, `last_name`]'),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('List all users')
            ->description('**Permission:** `Community Worker`')
            ->operationId('users.index')
            ->tags(Tags::users());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function store(): Operation
    {
        $responses = [
            Response::created()
                ->content(
                    MediaType::json()->schema(UserResource::show())
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
                    'receive_booking_confirmations',
                    'receive_cancellation_confirmations',
                    'include_calendar_attachment',
                    'roles'
                )
                ->properties(
                    Schema::string('first_name'),
                    Schema::string('last_name'),
                    Schema::string('email'),
                    Schema::string('phone'),
                    Schema::string('password')->format(Schema::FORMAT_PASSWORD),
                    Schema::boolean('display_email'),
                    Schema::boolean('display_phone'),
                    Schema::boolean('receive_booking_confirmations'),
                    Schema::boolean('receive_cancellation_confirmations'),
                    Schema::boolean('include_calendar_attachment'),
                    Schema::array('roles')->items(
                        Schema::object()
                            ->required('role')
                            ->properties(
                                Schema::string('role'),
                                Schema::string('clinic_id')->format(Schema::FORMAT_UUID)
                            )
                    ),
                    Schema::string('profile_picture')->description('Base64 encoded JPEG.')
                )
        );

        return Operation::post()
            ->responses(...$responses)
            ->requestBody($requestBody)
            ->summary('Create a new user')
            ->description(
                <<<'EOT'
                **Permission:** `Clinic Admin`
                - Can create a user with the `Community Worker` role for a clinic that they are associated to
                
                **Permission:** `Organisation Admin`
                - Can create a user with any role for any clinic
                
                ***
                
                Create a new users along with their roles
                EOT
            )
            ->operationId('users.store')
            ->tags(Tags::users());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function show(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::json()->schema(UserResource::show())
                ),
        ];
        $parameters = [
            Parameter::path()
                ->name('user')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('The user ID')
                ->required(),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('Get a specific user')
            ->description('**Permission:** `Community Worker`')
            ->operationId('users.show')
            ->tags(Tags::users());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function user(): Operation
    {
        $responses = [
            Response::ok()
                ->content(
                    MediaType::json()->schema(UserResource::show())
                ),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->summary('Get the logged in user')
            ->description('**Permission:** `Community Worker`')
            ->operationId('users.user')
            ->tags(Tags::users());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function update(): Operation
    {
        $responses = [
            Response::ok()->content(
                MediaType::json()->schema(UserResource::show())
            ),
        ];
        $parameters = [
            Parameter::path()
                ->name('user')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
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
                    'receive_booking_confirmations',
                    'receive_cancellation_confirmations',
                    'include_calendar_attachment',
                    'roles'
                )
                ->properties(
                    Schema::string('first_name'),
                    Schema::string('last_name'),
                    Schema::string('email'),
                    Schema::string('phone'),
                    Schema::string('password')->format(Schema::FORMAT_PASSWORD),
                    Schema::boolean('display_email'),
                    Schema::boolean('display_phone'),
                    Schema::boolean('receive_booking_confirmations'),
                    Schema::boolean('receive_cancellation_confirmations'),
                    Schema::boolean('include_calendar_attachment'),
                    Schema::array('roles')->items(
                        Schema::object()
                            ->required('role')
                            ->properties(
                                Schema::string('role'),
                                Schema::string('clinic_id')->format(Schema::FORMAT_UUID)
                            )
                    ),
                    Schema::string('profile_picture')
                        ->nullable()
                        ->description('Base64 encoded JPEG.')
                )
        );

        return Operation::put()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->requestBody($requestBody)
            ->summary('Update a specified user')
            ->description(
                <<<'EOT'
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
                EOT
            )
            ->operationId('users.update')
            ->tags(Tags::users());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function destroy(): Operation
    {
        $responses = [
            Response::ok()->content(
                MediaType::json()->schema(UserResource::deleted())
            ),
        ];
        $parameters = [
            Parameter::path()
                ->name('user')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('The user ID')
                ->required(),
        ];

        return Operation::delete()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('Delete a specific user')
            ->description(
                <<<'EOT'
                **Permission:** `Organisation Admin`
                
                ***
                
                This will only disable the user so they can no longer access the backend. All of this user's future appointments
                will also be cancelled and deleted.
                EOT
            )
            ->operationId('users.destroy')
            ->tags(Tags::users());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function profilePicture(): Operation
    {
        $responses = [
            Response::ok()->content(
                MediaType::jpeg()->schema(
                    Schema::string()->format(Schema::FORMAT_BINARY)
                )
            ),
        ];
        $parameters = [
            Parameter::path()
                ->name('user')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('The user ID')
                ->required(),
        ];

        return Operation::get()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('Get a specific user\'s profile picture')
            ->description('**Permission:** `Open`')
            ->operationId('users.profile-picture')
            ->tags(Tags::users());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function calendarFeedToken(): Operation
    {
        $responses = [
            Response::created()->content(
                MediaType::json()->schema(
                    Schema::object()
                        ->properties(
                            Schema::string('calendar_feed_token')
                        )
                )
            ),
        ];
        $parameters = [
            Parameter::path()
                ->name('user')
                ->schema(Schema::string()->format(Schema::FORMAT_UUID))
                ->description('The user ID')
                ->required(),
        ];

        return Operation::put()
            ->responses(...$responses)
            ->parameters(...$parameters)
            ->summary('Refresh the calendar feed token')
            ->description(
                <<<'EOT'
                **Permission:** `Community Worker`
                - Can refresh their own calendar feed token
                EOT
            )
            ->operationId('users.calendar-feed-token')
            ->tags(Tags::users());
    }

    /**
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function destroySessions(): Operation
    {
        $responses = [
            Response::ok()->content(
                MediaType::json()->schema(
                    Schema::object()->properties(
                        Schema::string('message')->example('All your sessions have been cleared.')
                    )
                )
            ),
        ];

        return Operation::delete()
            ->responses(...$responses)
            ->summary('Clear the sessions for the authenticated user')
            ->description(
                <<<'EOT'
                **Permission:** `Community Worker`
                EOT
            )
            ->operationId('users.sessions.destroy')
            ->tags(Tags::users());
    }
}

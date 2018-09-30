<?php

namespace App\Docs\Paths;

use App\Docs\Requests;
use App\Docs\Resources\SettingResource;
use App\Docs\Responses;
use App\Docs\Tags;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;

class Settings
{
    /**
     * Settings constructor.
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
                MediaType::json(SettingResource::show())
            ),
        ];

        return Operation::get(...$responses)
            ->summary('List all the organisation settings')
            ->description('**Permission:** `Open`')
            ->operationId('settings.index')
            ->tags(Tags::settings()->name);
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Operation
     */
    public static function update(): Operation
    {
        $responses = [
            Responses::http200(
                MediaType::json(SettingResource::show())
            ),
        ];
        $requestBody = Requests::json(SettingResource::resource());

        return Operation::put(...$responses)
            ->requestBody($requestBody)
            ->summary('Update all of the organisation settings')
            ->description('**Permission:** `Organisation Admin`')
            ->operationId('settings.update')
            ->tags(Tags::settings()->name);
    }
}

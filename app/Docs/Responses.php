<?php

declare(strict_types=1);

namespace App\Docs;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use Illuminate\Http\Response as IlluminateResponse;

class Responses
{
    /**
     * Responses constructor.
     */
    protected function __construct()
    {
        // Prevent instantiation.
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType[] $content
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Response
     */
    public static function http200(MediaType ...$content): Response
    {
        return Response::create(
            IlluminateResponse::HTTP_OK,
            'Successful response',
            ...$content
        );
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType[] $content
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Response
     */
    public static function http201(MediaType ...$content): Response
    {
        return Response::create(
            IlluminateResponse::HTTP_CREATED,
            'Resource created',
            ...$content
        );
    }
}

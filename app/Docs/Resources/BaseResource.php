<?php

declare(strict_types=1);

namespace App\Docs\Resources;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

abstract class BaseResource
{
    /**
     * BaseResource constructor.
     */
    protected function __construct()
    {
        // Prevent instantiation.
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema
     */
    abstract public static function resource(): Schema;

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema
     */
    public static function show(): Schema
    {
        return Schema::object()
            ->properties(static::resource()->name('data'));
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema
     */
    public static function list(): Schema
    {
        return Schema::object()
            ->properties(
                Schema::array('data')
                    ->items(static::resource()),
                Schema::object('link')
                    ->properties(
                        Schema::string('first')->example('https://api.example.com/v1/resource?page=1'),
                        Schema::string('last')->example('https://api.example.com/v1/resource?page=10'),
                        Schema::string('prev')->example('https://api.example.com/v1/resource?page=4')->nullable(),
                        Schema::string('next')->example('https://api.example.com/v1/resource?page=6')->nullable()
                    ),
                Schema::object('meta')
                    ->properties(
                        Schema::integer('current_page')->example(5),
                        Schema::integer('from')->example(51),
                        Schema::integer('last_page')->example(10),
                        Schema::string('path')->example('https://api.example.com/v1/resource'),
                        Schema::integer('per_page')->example(10),
                        Schema::integer('to')->example(60),
                        Schema::integer('total')->example(100)
                    )
            );
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema
     */
    public static function all(): Schema
    {
        return Schema::object()
            ->properties(
                Schema::array('data')->items(static::resource())
            );
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema
     */
    public static function deleted(): Schema
    {
        return Schema::object()
            ->properties(
                Schema::string('message')->example('Resource deleted')
            );
    }
}

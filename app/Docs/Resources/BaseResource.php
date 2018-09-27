<?php

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
        return static::single(static::resource());
    }

    /**
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema
     */
    public static function list(): Schema
    {
        return static::collection(static::resource());
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema $resource
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema
     */
    public static function collection(Schema $resource): Schema
    {
        return Schema::object()
            ->properties(
                Schema::array('data')
                    ->items($resource),
                Schema::object('link')
                    ->properties(),
                Schema::object('meta')
                    ->properties()
            );
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema $resource
     * @return \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema
     */
    public static function single(Schema $resource): Schema
    {
        return Schema::object()
            ->properties(
                Schema::object('data')
                    ->properties($resource)
            );
    }
}

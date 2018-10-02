<?php

namespace App\Rules;

use Illuminate\Support\Carbon;

class DateFormat
{
    /**
     * DateFormat constructor.
     */
    protected function __construct()
    {
        //
    }

    /**
     * @param string $format
     * @return string
     */
    public static function format(string $format): string
    {
        return 'date_format:' . $format;
    }

    /**
     * @return string
     */
    public static function iso8601(): string
    {
        return static::format(Carbon::ISO8601);
    }
}

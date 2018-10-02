<?php

namespace App\Rules;

use App\Support\Carbon;

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
    public function __invoke(string $format): string
    {
        return 'date_format:'.$format;
    }

    /**
     * @return string
     */
    public static function iso8601(): string
    {
        return static(Carbon::ISO8601);
    }
}

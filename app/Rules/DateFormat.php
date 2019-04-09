<?php

namespace App\Rules;

use Illuminate\Support\Carbon;

class DateFormat
{
    /**
     * @var string
     */
    protected $format;

    /**
     * DateFormat constructor.
     *
     * @param string $format
     */
    public function __construct(string $format)
    {
        $this->format = $format;
    }

    /**
     * @return \App\Rules\DateFormat
     */
    public static function iso8601(): self
    {
        return new static(Carbon::ATOM);
    }

    /**
     * @return \App\Rules\DateFormat
     */
    public static function date(): self
    {
        return new static('Y-m-d');
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "date_format:{$this->format}";
    }
}

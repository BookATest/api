<?php

namespace App\Support;

use InvalidArgumentException;

class Postcode
{
    const PATTERN = '/^([Gg][Ii][Rr] 0[Aa]{2})|((([A-Za-z][0-9]{1,2})|(([A-Za-z][A-Ha-hJ-Yj-y][0-9]{1,2})|(([A-Za-z][0-9][A-Za-z])|([A-Za-z][A-Ha-hJ-Yj-y][0-9]?[A-Za-z]))))\s[0-9][A-Za-z]{2})$/';

    /**
     * @var string
     */
    protected $postcode;

    /**
     * Postcode constructor.
     *
     * @param string $postcode
     */
    public function __construct(string $postcode)
    {
        $this->set($postcode);
    }

    /**
     * @return string
     */
    public function get(): string
    {
        return $this->postcode;
    }

    /**
     * @param string $postcode
     * @return \App\Support\Postcode
     */
    public function set(string $postcode): self
    {
        if (!static::validate($postcode)) {
            throw new InvalidArgumentException('The postcode is invalid');
        }

        $this->postcode = $postcode;

        return $this;
    }

    /**
     * @param string $postcode
     * @return bool
     */
    public static function validate(string $postcode): bool
    {
        return (bool)preg_match(static::PATTERN, $postcode);
    }
}

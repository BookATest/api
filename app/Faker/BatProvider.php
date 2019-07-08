<?php

declare(strict_types=1);

namespace App\Faker;

use Faker\Provider\Base;

class BatProvider extends Base
{
    /**
     * @return string
     */
    public function ukMobileNumber(): string
    {
        return '07' . mt_rand(100000000, 999999999);
    }
}

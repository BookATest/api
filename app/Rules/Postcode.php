<?php

namespace App\Rules;

use App\Contracts\Geocoder;
use App\Exceptions\AddressNotFoundException;
use Illuminate\Contracts\Validation\Rule;

class Postcode implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $postcode
     * @return bool
     */
    public function passes($attribute, $postcode)
    {
        if (!is_string($postcode)) {
            return false;
        }

        if (!\App\Support\Postcode::validate($postcode)) {
            return false;
        }

        /** @var \App\Contracts\Geocoder $geocoder */
        $geocoder = resolve(Geocoder::class);

        try {
            $geocoder->geocode(new \App\Support\Postcode($postcode));
        } catch (AddressNotFoundException $exception) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The postcode is invalid.';
    }
}

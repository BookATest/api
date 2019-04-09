<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class AppointmentOutsideDstTransition implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $startAt
     * @return bool
     */
    public function passes($attribute, $startAt)
    {
        if (!is_string($startAt)) {
            return false;
        }

        try {
            /** @var \Illuminate\Support\Carbon $startAt */
            $startAt = Carbon::createFromFormat(Carbon::ATOM, $startAt);
            $startAt = $startAt->second(0);
        } catch (InvalidArgumentException $exception) {
            return false;
        }

        // Convert the posted start date to the correct timezone.
        $timezonedStartAt = $startAt->copy()->timezone(config('app.timezone'));

        // Check if the time remains the same, the conversion won't allow the skipped hours.
        return $startAt->toIso8601String() === $timezonedStartAt->toIso8601String();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute cannot fall within a transitioning hour to/from daylight savings time.';
    }
}

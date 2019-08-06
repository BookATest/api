<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Base64FileSize implements Rule
{
    /**
     * @var float
     */
    protected $maxSizeInMegaBytes;

    /**
     * Create a new rule instance.
     *
     * @param float $maxSizeInMegaBytes
     */
    public function __construct(float $maxSizeInMegaBytes)
    {
        $this->maxSizeInMegaBytes = $maxSizeInMegaBytes;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @link https://stackoverflow.com/a/48416269
     * @param string $attribute
     * @param mixed $base64EncodedImage
     * @return bool
     */
    public function passes($attribute, $base64EncodedImage)
    {
        if (!is_string($base64EncodedImage)) {
            return false;
        }

        list(, $data) = explode(';', $base64EncodedImage);
        list(, $data) = explode(',', $data);
        $data = rtrim($data, '=');
        $sizeInBytes = (int)(mb_strlen($data) * 3 / 4);
        $sizeInKiloBytes = $sizeInBytes / 1024;
        $sizeInMegaBytes = $sizeInKiloBytes / 1024;

        return $sizeInMegaBytes <= $this->maxSizeInMegaBytes;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $size = round($this->maxSizeInMegaBytes, 2);

        return "The :attribute must not be larger than {$size}MB.";
    }
}

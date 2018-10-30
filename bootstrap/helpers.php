<?php

if (!function_exists('uuid')) {
    /**
     * Generate a UUID (version 4).
     *
     * @return string
     */
    function uuid(): string
    {
        return \Illuminate\Support\Str::uuid()->toString();
    }
}

if (!function_exists('crop_and_resize')) {
    /**
     * @param string $content
     * @param int $width
     * @param int $height
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    function crop_and_resize(string $content, int $width, int $height): string
    {
        // Store the contents into a temporary file.
        $sourceId = uuid();
        \Illuminate\Support\Facades\Storage::disk('temp')->put($sourceId, $content);
        $sourcePath = storage_path("temp/$sourceId");

        // Create a GD instance from the temporary file and a new file.
        $source = imagecreatefromjpeg($sourcePath);
        $destination = imagecreatetruecolor($width, $height);

        // Get the width and height from the source image.
        list($sourceWidth, $sourceHeight) = getimagesize($sourcePath);

        // Get the top left crop coordinates for the source image.
        if ($sourceWidth >= $sourceHeight) {
            $sourceX = floor(($sourceWidth - $sourceHeight) / 2);
            $sourceY = 0;
        } else {
            $sourceX = 0;
            $sourceY = floor(($sourceHeight - $sourceWidth) / 2);
        }

        // Get the cropped width and height for the source image.
        $croppedWidth = min($sourceWidth, $sourceHeight);
        $croppedHeight = $croppedWidth;

        imagecopyresampled(
            $destination,
            $source,
            0,
            0,
            $sourceX,
            $sourceY,
            $width,
            $height,
            $croppedWidth,
            $croppedHeight
        );

        // Get the contents of the destination file.
        $destinationId = uuid();
        $destinationPath = storage_path("temp/$destinationId");
        imagejpeg($destination, $destinationPath, 80);
        $destinationContent = \Illuminate\Support\Facades\Storage::disk('temp')->get($destinationId);

        // Delete the temporary files.
        \Illuminate\Support\Facades\Storage::disk('temp')->delete($sourceId);
        \Illuminate\Support\Facades\Storage::disk('temp')->delete($destinationId);

        return $destinationContent;
    }
}

if (!function_exists('base64_decode_image')) {
    /**
     * @param string $encodedImage
     * @return string
     */
    function base64_decode_image(string $encodedImage): string
    {
        list(, $data) = explode(';', $encodedImage);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);

        return $data;
    }
}

if (!function_exists('frontend_uri')) {
    /**
     * @param string|null $path
     * @return string
     */
    function frontend_uri(string $path = null): string
    {
        $uri = config('app.frontend_url');

        if ($path) {
            $uri .= '/' . ltrim($path, '/');
        }

        return $uri;
    }
}

if (!function_exists('backend_uri')) {
    /**
     * @param string|null $path
     * @return string
     */
    function backend_uri(string $path = null): string
    {
        $uri = config('app.backend_url');

        if ($path) {
            $uri .= '/' . ltrim($path, '/');
        }

        return $uri;
    }
}

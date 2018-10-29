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

        // Create a GD instance from the temporary file and a new file.
        $source = imagecreatefromjpeg(storage_path("temp/$sourceId"));
        $destination = imagecreatetruecolor($width, $height);

        // Get the width and height from the source image.
        list($sourceWidth, $sourceHeight) = getimagesize($source);

        // Calculate the aspect ratio of both the source, and the destination image.
        $sourceAspectRatio = $sourceWidth / $sourceHeight;
        $destinationAspectRatio = $width / $height;

        // Set the width and height of the area to crop.
        if ($sourceAspectRatio >= $destinationAspectRatio) {
            // If image is wider than thumbnail (in aspect ratio sense).
            $newHeight = $height;
            $newWidth = $width / ($height / $height);
        } else {
            // If the thumbnail is wider than the image.
            $newWidth = $width;
            $newHeight = $height / ($width / $width);
        }

        // Perform the image manipulation.
        imagecopyresampled(
            $destination,
            $source,
            0 - ($newWidth - $width) / 2,
            0 - ($newHeight - $height) / 2,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height
        );

        // Get the contents of the destination file.
        $destinationId = uuid();
        imagejpeg($destination, storage("temp/$destinationId"), 80);
        $destinationContent = \Illuminate\Support\Facades\Storage::disk('temp')->get($destinationId);

        // Delete the temporary files.
        \Illuminate\Support\Facades\Storage::disk('temp')->delete($sourceId);
        \Illuminate\Support\Facades\Storage::disk('temp')->delete($destinationId);

        return $destinationContent;
    }
}

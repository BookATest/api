<?php

namespace App\Models;

use App\Models\Mutators\SettingMutators;
use App\Models\Relationships\SettingRelationships;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    use SettingMutators;
    use SettingRelationships;

    const DEFAULT_APPOINTMENT_BOOKING_THRESHOLD = 'default_appointment_booking_threshold';
    const DEFAULT_APPOINTMENT_DURATION = 'default_appointment_duration';
    const LANGUAGE = 'language';
    const LOGO_FILE_ID = 'logo_file_id';
    const NAME = 'name';
    const PRIMARY_COLOUR = 'primary_colour';
    const SECONDARY_COLOUR = 'secondary_colour';
    const STYLES = 'styles';

    /**
     * @var string The primary key of the table.
     */
    protected $primaryKey = 'key';

    /**
     * Helper method to get the setting value.
     *
     * @param string $key
     * @return mixed
     */
    public static function getValue(string $key)
    {
        return static::findOrFail($key)->value;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAll(): Collection
    {
        return static::all()->mapWithKeys(function (Setting $setting) {
            return [$setting->key => $setting->value];
        });
    }

    /**
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function placeholderLogoPicture(): Response
    {
        $content = Storage::disk('local')->get('placeholders/organisation-logo.png');

        return response()->make($content, Response::HTTP_OK, [
            'Content-Type' => File::MIME_PNG,
            'Content-Disposition' => "inline; filename=\"organisation-logo.png\"",
        ]);
    }

    /**
     * @return \App\Models\File|null
     */
    public static function logoFile(): ?File
    {
        $fileId = static::getValue(static::LOGO_FILE_ID);

        return $fileId ? File::findOrFail($fileId) : null;
    }
}

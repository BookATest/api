<?php

namespace App\Models;

use App\Models\Mutators\SettingMutators;
use App\Models\Relationships\SettingRelationships;
use Illuminate\Database\Eloquent\Collection;

class Setting extends Model
{
    use SettingMutators;
    use SettingRelationships;

    const DEFAULT_APPOINTMENT_BOOKING_THRESHOLD = 'default_appointment_booking_threshold';
    const DEFAULT_APPOINTMENT_DURATION = 'default_appointment_duration';
    const DEFAULT_NOTIFICATION_MESSAGE = 'default_notification_message';
    const DEFAULT_NOTIFICATION_SUBJECT = 'default_notification_subject';
    const LANGUAGE = 'language';
    const LOGO_FILE_ID = 'logo_file_id';
    const NAME = 'name';
    const PRIMARY_COLOUR = 'primary_colour';
    const SECONDARY_COLOUR = 'secondary_colour';

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
}

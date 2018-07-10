<?php

namespace App\Models\Mutators;

trait SettingMutators
{
    /*
     * Value.
     */

    public function getValueAttribute(string $value)
    {
        return json_decode($value);
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = json_encode($value);
    }
}

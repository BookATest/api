<?php

namespace App\Models;

use App\Models\Relationships\SettingRelationships;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use SettingRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
    ];
}

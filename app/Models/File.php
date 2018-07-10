<?php

namespace App\Models;

use App\Models\Relationships\FileRelationships;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use FileRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'filename',
        'mime_type',
    ];
}

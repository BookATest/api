<?php

namespace App\Models;

use App\Models\Relationships\NotificationRelationships;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use NotificationRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'notifiable_id',
        'notifiable_type',
        'channel',
        'recipient',
        'message',
    ];
}

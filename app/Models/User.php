<?php

namespace App\Models;

use App\Models\Mutators\UserMutators;
use App\Models\Relationships\UserRelationships;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;
    use UserMutators;
    use UserRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'display_email',
        'display_phone',
        'include_calendar_attachment',
        'calendar_feed_token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'calendar_feed_token',
        'remember_token',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'disabled_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'display_phone' => 'boolean',
        'display_email' => 'boolean',
        'include_calendar_attachment' => 'boolean',
    ];
}

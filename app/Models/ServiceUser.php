<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceUser extends Model
{
    /**
     * @var string The primary key of the table.
     */
    protected $primaryKey = 'uuid';

    /**
     * @var bool If the primary key is an incrementing value.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'preferred_contact_method',
    ];
}

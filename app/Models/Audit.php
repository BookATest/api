<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'auditable_id',
        'auditable_type',
        'client_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
    ];
}

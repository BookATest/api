<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    const COMMUNITY_WORKER = 'community_worker';
    const CLINIC_ADMIN = 'clinic_admin';
    const ORGANISATION_ADMIN = 'organisation_admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    const COMMUNITY_WORKER = 'Community Worker';
    const CLINIC_ADMIN = 'Clinic Admin';
    const ORGANISATION_ADMIN = 'Organisation Admin';
}

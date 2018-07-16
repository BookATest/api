<?php

namespace App\Models;

use App\Models\Mutators\AuditMutators;
use App\Models\Relationships\AuditRelationships;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    use AuditMutators;
    use AuditRelationships;

    const CREATE = 'create';
    const READ = 'read';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const LOGIN = 'login';
    const LOGOUT = 'logout';

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

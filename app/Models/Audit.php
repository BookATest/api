<?php

namespace App\Models;

use App\Models\Mutators\AuditMutators;
use App\Models\Relationships\AuditRelationships;

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
}

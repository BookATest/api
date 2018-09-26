<?php

namespace App\Models;

use App\Models\Mutators\UserRoleMutators;
use App\Models\Relationships\UserRoleRelationships;

class UserRole extends Model
{
    use UserRoleMutators;
    use UserRoleRelationships;
}

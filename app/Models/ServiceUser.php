<?php

namespace App\Models;

use App\Models\Mutators\ServiceUserMutators;
use App\Models\Relationships\ServiceUserRelationships;
use Illuminate\Database\Eloquent\Model;

class ServiceUser extends Model
{
    use ServiceUserMutators;
    use ServiceUserRelationships;

    const PHONE = 'phone';
    const EMAIL = 'email';

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

<?php

namespace App\Models;

use App\Models\Mutators\FileMutators;
use App\Models\Relationships\FileRelationships;

class File extends Model
{
    use FileMutators;
    use FileRelationships;
}

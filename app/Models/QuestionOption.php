<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Mutators\QuestionOptionMutators;
use App\Models\Relationships\QuestionOptionRelationships;

class QuestionOption extends Model
{
    use QuestionOptionMutators;
    use QuestionOptionRelationships;
}

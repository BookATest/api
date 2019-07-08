<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Mutators\AnswerMutators;
use App\Models\Relationships\AnswerRelationships;

class Answer extends Model
{
    use AnswerMutators;
    use AnswerRelationships;
}

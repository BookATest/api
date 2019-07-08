<?php

namespace App\Models;

use App\Models\Mutators\AnswerMutators;
use App\Models\Relationships\AnswerRelationships;

class Answer extends Model
{
    use AnswerMutators;
    use AnswerRelationships;
}

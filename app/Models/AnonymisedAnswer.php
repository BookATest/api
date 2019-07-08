<?php

namespace App\Models;

use App\Models\Mutators\AnonymisedAnswerMutators;
use App\Models\Relationships\AnonymisedAnswerRelationships;

class AnonymisedAnswer extends Model
{
    use AnonymisedAnswerMutators;
    use AnonymisedAnswerRelationships;
}

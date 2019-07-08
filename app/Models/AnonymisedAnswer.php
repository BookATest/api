<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Mutators\AnonymisedAnswerMutators;
use App\Models\Relationships\AnonymisedAnswerRelationships;

class AnonymisedAnswer extends Model
{
    use AnonymisedAnswerMutators;
    use AnonymisedAnswerRelationships;
}

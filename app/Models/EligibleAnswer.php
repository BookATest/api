<?php

namespace App\Models;

use App\Models\Mutators\EligibleAnswerMutators;
use App\Models\Relationships\EligibleAnswerRelationships;

class EligibleAnswer extends Model
{
    use EligibleAnswerMutators;
    use EligibleAnswerRelationships;
}

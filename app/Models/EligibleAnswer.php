<?php

namespace App\Models;

use App\Models\Mutators\EligibleAnswerMutators;
use App\Models\Relationships\EligibleAnswerRelationships;
use App\Models\Scopes\EligibleAnswerScopes;

class EligibleAnswer extends Model
{
    use EligibleAnswerMutators;
    use EligibleAnswerRelationships;
    use EligibleAnswerScopes;
}

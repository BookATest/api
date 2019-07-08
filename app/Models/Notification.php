<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Mutators\NotificationMutators;
use App\Models\Relationships\NotificationRelationships;

class Notification extends Model
{
    use NotificationMutators;
    use NotificationRelationships;

    const EMAIL = 'email';
    const SMS = 'sms';
}

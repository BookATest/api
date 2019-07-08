<?php

declare(strict_types=1);

namespace App\Models\Relationships;

use App\Models\Appointment;
use App\Models\Question;
use App\Models\ServiceUser;

trait AnswerRelationships
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}

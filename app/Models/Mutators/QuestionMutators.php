<?php

namespace App\Models\Mutators;

trait QuestionMutators
{
    /**
     * @return bool
     */
    public function getIsSelectAttribute(): bool
    {
        return $this->type === static::SELECT;
    }

    /**
     * @return bool
     */
    public function getIsCheckboxAttribute(): bool
    {
        return $this->type === static::CHECKBOX;
    }

    /**
     * @return bool
     */
    public function getIsDateAttribute(): bool
    {
        return $this->type === static::DATE;
    }

    /**
     * @return bool
     */
    public function getIsTextAttribute(): bool
    {
        return $this->type === static::TEXT;
    }

    /**
     * @return string[]
     */
    public function getAvailableOptionsAttribute(): array
    {
        return $this->questionOptions->map->option->toArray();
    }
}

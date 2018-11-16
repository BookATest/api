<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->onCreating($model);
        });

        static::created(function (Model $model) {
            $model->onCreated($model);
        });

        static::deleting(function (Model $model) {
            $model->onDeleting();
        });

        static::deleted(function (Model $model) {
            $model->onDeleted($model);
        });
    }

    /**
     * Called just before the model is created.
     *
     * @param \App\Models\Model $model
     */
    protected function onCreating(Model $model)
    {
        if (empty($model->{$model->getKeyName()})) {
            $model->{$model->getKeyName()} = uuid();
        }
    }

    /**
     * Called just after the model is created.
     *
     * @param \App\Models\Model $model
     */
    protected function onCreated(Model $model)
    {
        //
    }

    /**
     * Called just before the model is deleted.
     */
    protected function onDeleting()
    {
        //
    }

    /**
     * Called just after the model has deleted.
     *
     * @param \App\Models\Model $model
     */
    protected function onDeleted(Model $model)
    {
        //
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasAppend(string $name): bool
    {
        return in_array($name, $this->appends);
    }
}

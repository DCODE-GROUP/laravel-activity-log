<?php

namespace Dcodegroup\ActivityLog\Support\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property Carbon $created_at
 */
trait LastModifiedBy
{
    /**
     * Boot.
     */
    public static function bootLastModifiedBy()
    {
        static::creating(function (Model $model) {
            if (auth()->check()) {
                /** @phpstan-ignore-next-line */
                $model->updated_by = auth()->id();
                /** @phpstan-ignore-next-line */
                $model->created_by = auth()->id();
            }
        });

        static::updating(function (Model $model) {
            if (auth()->check()) {
                /** @phpstan-ignore-next-line */
                $model->updated_by = auth()->id();
            }
        });
    }
}

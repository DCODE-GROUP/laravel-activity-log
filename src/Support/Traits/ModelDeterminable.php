<?php

namespace Dcodegroup\ActivityLog\Support\Traits;

use Dcodegroup\ActivityLog\Exceptions\ModelKeyNotDefinedException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait ModelDeterminable
{
    public function determineModelKey(): string
    {
        /**
         * Check if the label has been set in the model
         */
        if (! empty($this->getActivityLogModelKey())) {
            return $this->getActivityLogModelKey();
        }

        $standardKeys = ['name', 'title', 'label'];

        foreach ($standardKeys as $key) {
            if (collect($this->getAttributes())->has($key)) {
                return $this->{$key};
            }
        }

        throw new ModelKeyNotDefinedException(__('activity-log.exceptions.model_key', ['model' => class_basename($this)]));
    }

    public function getActivityLogModelKey(): string
    {
        return '';
    }

    public function determineModelLabel(): string
    {
        /**
         * check if we have the model label in cache
         */
        if (Cache::has('model_label_'.class_basename($this))) {
            return Cache::get('model_label_'.class_basename($this));
        }

        /**
         * Check if the label has been set in the model
         */
        if (! empty($this->getActivityLogModelLabel())) {
            return Cache::rememberForever('model_label_'.class_basename($this), fn () => $this->getActivityLogModelLabel());
        }

        return Cache::rememberForever('model_label_'.class_basename($this), fn () => Str::headline(class_basename($this)));
    }

    public function getActivityLogModelLabel(): string
    {
        return '';
    }
}

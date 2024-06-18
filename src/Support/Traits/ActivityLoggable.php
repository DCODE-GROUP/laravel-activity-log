<?php

namespace Dcodegroup\ActivityLog\Support\Traits;

use Coduo\ToString\StringConverter;
use Dcodegroup\ActivityLog\Exceptions\ModelKeyNotDefinedException;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Models\CommunicationLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

trait ActivityLoggable
{
    /**
     * Available relationships for the model.
     *
     * @var array
     */
    protected static $availableRelations = [];

    public static function bootActivityLoggable()
    {
        static::created(function ($model) {
            $model->logCreate();
        });

        static::updating(function ($model) {
            $model->logUpdate();
        });

        static::deleting(function ($model) {
            $model->logDelete();
        });
    }

    public function logCreate(): void
    {
        $this->createActivityLog([
            'type' => ActivityLog::TYPE_DATA,
            'title' => __('activity-log.actions.create').$this->activityLogEntityName(),
        ]);
    }

    public function createActivityLog(array $description): ActivityLog
    {
        $diff = data_get($description, 'diff', []);

        if (count($diff) === 1 && data_get($diff, '0.key') === 'Status') {
            $description['title'] = Str::replace('updated', 'updated status for', $description['title']);
            $description['type'] = ActivityLog::TYPE_STATUS;
        }

        return $this->targetModel()->activityLogs()->create($description);
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'activitiable');
    }

    public function targetModel(): self|Model
    {
        return $this;
    }

    public function activityLogEntityName(): string
    {
        return Arr::join(Str::ucsplit(class_basename($this)), ' ').' (id:'.$this->id.')';
    }

    public function logUpdate(): void
    {
        $diff = $this->getModelChangesJson(); // true: If we want to limit the storage of fields defined in modelRelation; false : If we want to storage all model change

        if (collect($diff)->isNotEmpty()) {
            $this->createActivityLog([
                'title' => __('activity-log.actions.update').$this->activityLogEntityName(),
                'description' => $this->getModelChanges($diff),
            ]);
        }
    }

    public function getModelChangesJson(): array
    {
        return collect(array_keys($this->getDirty()))
            ->filter(fn ($item) => $this->getActivityLogModelAttributes()->has($item))
            ->map(function ($attribute) {

                $original = $this->getOriginal($attribute);
                $new = $this->{$attribute};

                /**
                 * Format the data if a custom formatter exists
                 */
                if ($formatter = $this->activityLogFieldFormatters()->get($attribute)) {
                    $original = $formatter($original);
                    $new = $formatter($new);
                }

                $from = is_array($original) ? collect($original)->map(fn ($item) => is_string($item) ? $item : new StringConverter($item))->join('|') : $original;
                $to = is_array($new) ? collect($new)->map(fn ($item) => is_string($item) ? $item : new StringConverter($item))->join('|') : (is_string($new) ? $new : new StringConverter($this->{$attribute}));

                return $this->prepareModelChange($attribute, $from, $to);
            })->toArray();
    }

    public function getActivityLogModelAttributes(): Collection
    {
        return collect(array_merge($this->getAttributes(), ['created_at', 'updated_at', 'deleted_at'], $this->getActivityLogModelExcludeFields()));
    }

    /**
     * This is where you will specify what fields to ignore on a per model basis. Eg extra fields to ignore
     */
    public function getActivityLogModelExcludeFields(): array
    {
        return [];
    }

    protected function activityLogFieldFormatters(): Collection
    {
        return collect([]);
    }

    public function prepareModelChange($attribute, $from, $to): array
    {
        $key = $attribute;

        ld('$attribute: '.$attribute);
        //        ld('model relation fields:', $this->getActivityLogModelRelationFields());

        //     getActivityLogModelRelationFields()

        if (in_array($attribute, collect($this->getActivityLogModelRelationFields())->pluck('foreignKey')->toArray())) {

            $relation = collect($this->getActivityLogModelRelationFields())->where('foreignKey', $attribute)->first();

            if (! empty($relation)) {
                //            $modelClass = array_flip($this->getActivityLogModelRelationFields())[$attribute];
                $modelClass = $relation['modelClass'];
                $from = $modelClass && $modelClass::find($from) ? $modelClass::find($from)->determineModelLabel() : '+';
                $to = $modelClass && $modelClass::find($to) ? $modelClass::find($to)->determineModelLabel() : '+';

                $key = $entity['label'];
            }
            ////            $modelClass = array_flip($this->getActivityLogModelRelationFields())[$attribute];
            //            $from = $modelClass && $modelClass::find($from) ? $modelClass::find($from)->{$entity['modelKey']} : '+';
            //            $to = $modelClass && $modelClass::find($to) ? $modelClass::find($to)->{$entity['modelKey']} : '+';
            //
            //            $key = $entity['label'];
        }

        return [
            'key' => $key,
            'from' => sprintf('<span class="activity__db-content">%s</span>', $from ?? '+'),
            'to' => sprintf('<span class="activity__db-content">%s</span>', $to ?? '+'),
        ];
    }

    public function getActivityLogModelRelationFields(): array
    {
        $model = new static();
        $relationships = [];

        foreach ((new ReflectionClass($model))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            ld('model class: ', $method->class);
            ld('class: ', get_class($model));
            ld('method: ', $method);
            ld('method name: '.$method->getName());
            ld('is sub class of relation', is_subclass_of((string) $method->getReturnType(), Relation::class));
            //            ld('params: ', $method->getParameters());
            ld('return type: ', $method->getReturnType());
            ld('getname is function ', ($method->getName() == __FUNCTION__));
            if (
                ! empty($method->getReturnType()) &&
                is_subclass_of((string) $method->getReturnType(), Relation::class)
            ) {

                $relationships[] = [
                    'method' => $method->getName(),
                    'relation' => $method->getReturnType()->getName(),
                    'foreignKey' => $model->{$method->getName()}()->getForeignKeyName(),
                    'localKey' => method_exists($model->{$method->getName()}(), 'getOwnerKeyName') ? $model->{$method->getName()}()->getOwnerKeyName() : $model->{$method->getName()}()->getLocalKeyName(),
                    'modelClass' => $method->class,
                    //                    'currentModel' => $model,
                ];
            }
        }

        return $relationships;
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

    public function getModelChanges(?array $modelChangesJson = null): string
    {
        return collect($modelChangesJson ?: $this->getModelChangesJson())->map(function ($row) {
            $attribute = $row['key'];
            $from = $row['from'];
            $to = $row['to'];

            return sprintf('%s: %s -> %s', '<b>'.Str::ucfirst(Str::replace('_', ' ', $attribute)).'</b>', '<b style="text-decoration: line-through;">'.$from.'</b>', '<b>'.$to.'</b>');
        })->join('<br>');
    }

    public function logDelete(): void
    {
        $this->createActivityLog([
            'title' => __('activity-log.actions.delete').$this->activityLogEntityName(),
            'description' => '',
        ]);
    }

    /**
     * Stores relationships for future use
     *
     * @return array
     */
    public static function setAvailableRelations(array $relations)
    {
        static::$availableRelations[static::class] = $relations;

        return $relations;
    }

    public function determineModelKey(): string
    {
        /**
         * check if we have the model label in cache
         */
        if (Cache::has('model_key_'.class_basename($this))) {
            return Cache::get('model_key_'.class_basename($this));
        }

        /**
         * Check if the label has been set in the model
         */
        if (! empty($this->getActivityLogModelKey())) {
            return Cache::rememberForever('model_key_'.class_basename($this), fn () => $this->getActivityLogModelKey());
        }

        $standardKeys = ['name', 'title', 'label'];

        foreach ($standardKeys as $key) {
            if (collect($this->getAttributes())->has($key)) {
                return Cache::rememberForever('model_key_'.class_basename($this), fn () => $this->{$key});
            }
        }

        throw new ModelKeyNotDefinedException(__('activity-log.exceptions.model_key_not_defined', ['model' => class_basename($this)]));
    }

    public function getActivityLogModelKey(): string
    {
        return '';
    }

    public function getActivityLogEmails(): array
    {
        return [];
    }

    public function activities(): Collection
    {
        $model = collect([$this->loadMissing($this->activityRelations()->toArray())]);

        $rewrittenRelations = $this->activityRelations()->map(fn ($relations) => collect(explode('.', $relations))
            ->map(function ($relation) {
                if ($relation[strlen($relation) - 1] === 's' && $relation[strlen($relation) - 2] !== 's') {
                    return $relation.'.*';
                }

                return $relation;
            })->join('.'));

        return $rewrittenRelations->map(fn ($relation) => $model->pluck($relation)->flatten())->flatten(1);
    }

    protected function activityRelations(): Collection
    {
        return collect([
            'activityLogs',
        ]);
    }

    public function createCommunicationLog(array $data, string $to, string $content, string $type = CommunicationLog::TYPE_EMAIL): CommunicationLog
    {
        $cc = collect($data['cc'])->map(fn ($item) => $item instanceof Address ? $item->address : $item)->join(', ');
        $bcc = collect($data['bcc'])->map(fn ($item) => $item instanceof Address ? $item->address : $item)->join(', ');

        return CommunicationLog::query()->create([
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'subject' => $data['subject'],
            'content' => $content,
            'type' => $type,
        ]);
    }

    protected function modelRelation(): Collection
    {
        return collect([]);
    }
}

<?php

namespace Dcodegroup\ActivityLog\Support\Traits;

use Coduo\ToString\StringConverter;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Models\CommunicationLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

trait ActivityLoggable
{
    use ModelDeterminable;

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
        $data = [
            'type' => ActivityLog::TYPE_DATA,
            'title' => __('activity-log.actions.create').$this->activityLogEntityName(),
        ];

        if (session()->has('session_uuid')) {
            $data['session_uuid'] = session('session_uuid');
        }

        $this->createActivityLog($data);
    }

    public function activityLogEntityName(): string
    {
        return Arr::join(Str::ucsplit(class_basename($this)), ' ').' (id:'.$this->id.')';
    }

    public function createActivityLog(array $data): ActivityLog
    {
        $diff = data_get($data, 'diff', []);

        if (count($diff) === 1 && data_get($diff, '0.key') === 'Status') {
            $data['title'] = Str::replace('updated', 'updated status for', $data['title']);
            $data['type'] = ActivityLog::TYPE_STATUS;
        }

        if (Arr::exists($data, 'session_uuid')) {
            return $this->targetModel()->activityLogs()->updateOrCreate(
                ['session_uuid' => $data['session_uuid']],
                Arr::except($data, 'session_uuid')
            );
        }

        return $this->targetModel()->activityLogs()->create($data);
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'activitiable');
    }

    public function targetModel(): self|Model
    {
        return $this;
    }

    public function logUpdate(): void
    {
        $diff = $this->getModelChangesJson(); // true: If we want to limit the storage of fields defined in modelRelation; false : If we want to storage all model change

        if (collect($diff)->isNotEmpty()) {
            $this->createActivityLog([
                'title' => __('activity-log.actions.update').$this->activityLogEntityName(),
                'description' => $this->getModelChanges($diff),
                'session_uuid' => session('session_uuid'),
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

                $from = $this->formatValue($original);
                $to = $this->formatValue($new);

                try {
                    return $this->prepareModelChange($attribute, $from, $to);
                } catch (Throwable $t) {
                    return null;
                }
            })
            ->filter()
            ->toArray();
    }

    public function getActivityLogModelAttributes(): Collection
    {
        return collect(array_merge($this->getAttributes(), ['created_at', 'updated_at', 'deleted_at', 'id', 'password'], $this->getActivityLogModelExcludeFields()));
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

    private function formatValue($value)
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => is_string($item) ? $item : new StringConverter($item))
                ->join('|');
        }

        return is_string($value) ? $value : new StringConverter($value);
    }

    public function prepareModelChange($attribute, $from, $to): array
    {
        $key = $attribute;

        if (in_array($attribute, collect($this->getActivityLogModelRelationFields())->pluck('foreignKey')->toArray())) {
            $relation = collect($this->getActivityLogModelRelationFields())->where('foreignKey', $attribute)->first();

            if (! empty($relation)) {
                $modelClass = $relation['modelClass'];
                $from = $modelClass && $modelClass::find($from) ? ($modelClass::find($from))->determineModelKey() : '+';
                $to = $modelClass && $modelClass::find($to) ? ($modelClass::find($to))->determineModelKey() : '+';

                $key = (new $modelClass)->determineModelLabel();
            }
        }

        return [
            'key' => $key,
            'from' => sprintf('<span class="activity__db-content">%s</span>', $from ?? '+'),
            'to' => sprintf('<span class="activity__db-content">%s</span>', $to ?? '+'),
        ];
    }

    public function getActivityLogModelRelationFields(): array
    {
        $model = new static;
        $relationships = [];

        foreach ((new ReflectionClass($model))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (
                ! empty($method->getReturnType()) &&
                is_subclass_of((string) $method->getReturnType(), Relation::class)
            ) {

                $relationMethod = $model->{$method->getName()}();
                $foreignKey = match (true) {
                    method_exists($relationMethod, 'getForeignKeyName') => $relationMethod->getForeignKeyName(),
                    default => $relationMethod->getForeignPivotKeyName(),
                };

                $localKey = match (true) {
                    method_exists($relationMethod, 'getOwnerKeyName') => $relationMethod->getOwnerKeyName(),
                    method_exists($relationMethod, 'getLocalKeyName') => $relationMethod->getLocalKeyName(),
                    default => $relationMethod->getRelatedPivotKeyName(),
                };

                $relationships[] = [
                    'method' => $method->getName(),
                    'relation' => $method->getReturnType()->getName(),
                    'foreignKey' => $foreignKey,
                    'localKey' => $localKey,
                    'modelClass' => $model->{$method->getName()}()->getRelated(),
                ];
            }
        }

        return $relationships;
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

    public function getMentionCommentUrl(Model $model): string
    {
        return '';
    }
}

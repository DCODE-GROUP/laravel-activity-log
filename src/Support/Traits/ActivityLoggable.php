<?php

namespace Dcodegroup\ActivityLog\Support\Traits;

use Coduo\ToString\StringConverter;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Models\CommunicationLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait ActivityLoggable
{
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
            'title' => __('activity-log.actions.create').' #'.$this->id,
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

    public function logUpdate(): void
    {
        $diff = $this->getModelChangesJson(true); // true: If we want to limit the storage of fields defined in modelRelation; false : If we want to storage all model change
        $this->createActivityLog([
            'title' => __('activity-log.actions.update').' #'.$this->id,
            'description' => $this->getModelChanges($diff),
        ]);
    }

    public function getModelChangesJson(bool $allowCustomAttribute = false): array
    {
        $attributes = collect(array_keys($this->getDirty()));
        if ($allowCustomAttribute) {
            $attributes = $attributes->filter(fn ($item) => $this->modelRelation()->has($item));
        }

        return $attributes->map(function ($attribute) {
            $from = is_array($this->getOriginal($attribute)) ? collect($this->getOriginal($attribute))->join('|') : $this->getOriginal($attribute);
            $to = is_array($this->{$attribute}) ? collect($this->{$attribute})->join('|') : (is_string($this->{$attribute}) ? $this->{$attribute} : new StringConverter($this->{$attribute}));

            return $this->prepareModelChange($attribute, $from, $to);
        })->toArray();
    }

    protected function modelRelation(): Collection
    {
        return collect([]);
    }

    public function prepareModelChange($attribute, $from, $to): array
    {
        ld('attribute', $attribute, 'from', $from, 'to', $to);
        $key = $attribute;

        if ($entity = $this->modelRelation()->get($attribute)) {
            $modelClass = $entity['modelClass'];
            $from = $modelClass && $modelClass::find($from) ? $modelClass::find($from)->{$entity['modelKey']} : '+';
            $to = $modelClass && $modelClass::find($to) ? $modelClass::find($to)->{$entity['modelKey']} : '+';

            $key = $entity['label'];
            //            return [
            //                'key' => $entity['label'],
            //                'from' => sprintf('<span class="activity__db-content">%s</span>', $formLabel),
            //                'to' => sprintf('<span class="activity__db-content">%s</span>', $toLabel),
            //            ];
        }

        if ($formatter = $this->activityLogFieldFormatters()->get($attribute)) {
            ld('got into formatter. it is', $formatter);
            //            ld('formatter', $formatter);
            $from = $formatter($from);
            $to = $formatter($to);
        }

        return [
            'key' => $key,
            'from' => sprintf('<span class="activity__db-content">%s</span>', $from ?? '+'),
            'to' => sprintf('<span class="activity__db-content">%s</span>', $to ?? '+'),
        ];
    }

    protected function activityLogFieldFormatters(): Collection
    {
        return collect([]);
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
            'title' => __('activity-log.actions.delete').' #'.$this->id,
            'description' => '',
        ]);
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
}

<?php

namespace Dcodegroup\ActivityLog\Support\Traits;

use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Models\CommunicationLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait ActivityLoggable
{
    protected function modelRelation(): Collection
    {
        return collect([]);
    }

    protected function activityRelations(): Collection
    {
        return collect([
            'activityLogs',
        ]);
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

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'activitiable');
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

    public function getModelChangesJson(): array
    {
        return collect(array_keys($this->getDirty()))->map(function ($attribute) {
            $from = is_array($this->getOriginal($attribute)) ? collect($this->getOriginal($attribute))->join('|') : $this->getOriginal($attribute);
            $to = is_array($this->{$attribute}) ? collect($this->{$attribute})->join('|') : $this->{$attribute};

            return $this->prepareModelChange($attribute, $from, $to);
        })->toArray();
    }

    public function prepareModelChange($attribute, $from, $to): array
    {
        if ($entity = $this->modelRelation()->get($attribute)) {
            $modelClass = $entity['modelClass'];
            $formLabel = $modelClass && $modelClass::find($from) ? $modelClass::find($from)->{$entity['modelKey']} : '+';
            $toLabel = $modelClass && $modelClass::find($to) ? $modelClass::find($to)->{$entity['modelKey']} : '+';

            return [
                'key' => $entity['label'],
                'from' => sprintf('<span class="activity__db-content">%s</span>', $formLabel),
                'to' => sprintf('<span class="activity__db-content">%s</span>', $toLabel),
            ];
        } else {
            return [
                'key' => $attribute,
                'from' => sprintf('<span class="activity__db-content">%s</span>', $from ?? '+'),
                'to' => sprintf('<span class="activity__db-content">%s</span>', $to ?? '+'),
            ];
        }
    }

    public function createActivityLog(array $description): ActivityLog
    {
        return $this->activityLogs()->create($description);
    }

    public function createCommunicationLog(array $data, string $to, string $content, string $type = CommunicationLog::TYPE_EMAIL): CommunicationLog
    {
        return CommunicationLog::query()->create([
            'to' => $to,
            'cc' => implode(', ', $data['cc']),
            'bcc' => implode(', ', $data['bcc']),
            'subject' => $data['subject'],
            'content' => $content,
            'type' => $type,
        ]);
    }
}

<?php

namespace Dcodegroup\ActivityLog\Resources;

use Dcodegroup\ActivityLog\Models\ActivityLog as ActivityLogModel;
use Dcodegroup\ActivityLog\Models\CommunicationLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ActivityLogModel $resource
 */
class ActivityLog extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'user' => $this->resource->loadMissing('user')->user?->getActivityLogUserName() ?: 'System',
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'activitiable_id' => $this->resource->activitiable_id,
            'activitiable_type' => $this->resource->activitiable_type,
            'type' => $this->resource->type,
            'meta' => $this->resource->meta,
            'created_at' => $this->resource->created_at->diffForHumans(),
            'created_at_date' => $this->resource->created_at->format(config('activity-log.datetime_format')),
            'communication' => $this->getCommunicationLog(),
            'icon' => ActivityLogModel::ICON_TYPE_MAP[$this->resource->type],
            'color' => ActivityLogModel::COLOR_TYPE_MAP[$this->resource->type],
        ];
    }

    private function getCommunicationLog(): ?array
    {
        if (! $this->resource->communicationLog instanceof CommunicationLog) {
            return null;
        }

        return [
            'id' => $this->resource->communicationLog->id,
            'type' => $this->resource->communicationLog->type,
            'to' => $this->resource->communicationLog->to,
            'subject' => $this->resource->communicationLog->subject,
            'content' => $this->resource->communicationLog->content,
            'icon' => CommunicationLog::ICON_TYPE_MAP[$this->resource->communicationLog->type],
        ];
    }
}

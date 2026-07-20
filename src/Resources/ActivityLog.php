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
        $createdAt = $this->resource->created_at->setTimezone($request->input('timezone', config('app.timezone', 'UTC')))->diffForHumans();
        $createdDate = $this->resource->created_at->setTimezone($request->input('timezone', config('app.timezone', 'UTC')))->format(config('activity-log.datetime_format'));

        // ensure reactions relationship is loaded
        $this->resource->loadMissing('reactions.user');

        // build reaction groups and counts
        $reactions = $this->resource->reactions ?? collect();
        $groups = [];
        foreach ($reactions as $r) {
            $emoji = $r->emoji;
            if (! isset($groups[$emoji])) {
                $groups[$emoji] = [];
            }
            $groups[$emoji][] = [
                'id' => $r->id,
                'user' => $r->user ? [
                    'id' => $r->user->id ?? null,
                    'full_name' => $r->user->getActivityLogUserName() ?? ($r->user->full_name ?? $r->user->name ?? $r->user->email ?? null),
                ] : null,
            ];
        }

        $counts = [];
        foreach ($groups as $emoji => $items) {
            $counts[$emoji] = count($items);
        }

        // determine current user's reaction (from request or auth)
        $currentUserId = null;
        if ($request->input('currentUser') && is_array($request->input('currentUser')) && isset($request->input('currentUser')['id'])) {
            $currentUserId = $request->input('currentUser')['id'];
        } elseif (function_exists('auth') && auth()->check()) {
            $currentUserId = auth()->id();
        }

        $currentUserReaction = null;
        foreach ($reactions as $r) {
            if ($currentUserId && $r->user && $r->user->id == $currentUserId) {
                $currentUserReaction = $r->emoji;
                break;
            }
        }

        return [
            'id' => $this->resource->id,
            'user' => $this->resource->loadMissing('user')->user?->getActivityLogUserName() ?: 'System',
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'is_edited' => $this->resource->created_at->ne($this->resource->updated_at),
            'meta' => $this->resource->meta,
            'activitiable_id' => $this->resource->activitiable_id,
            'activitiable_type' => $this->resource->activitiable_type,
            'type' => $this->resource->type,
            'created_at' => $createdAt,
            'created_at_date' => $createdDate,
            'communication' => $this->getCommunicationLog(),
            'icon' => ActivityLogModel::ICON_TYPE_MAP[$this->resource->type] ?? ActivityLogModel::ICON_TYPE_MAP[ActivityLogModel::TYPE_DATA],
            'color' => ActivityLogModel::COLOR_TYPE_MAP[$this->resource->type] ?? ActivityLogModel::COLOR_TYPE_MAP[ActivityLogModel::TYPE_DATA],
            'delete_comment_endpoint' => $this->resource->type === ActivityLogModel::TYPE_COMMENT ? route(config('activity-log.route_name').'.comment.delete', $this->resource->id) : null,

            // reactions (use both snake_case and camelCase to match different clients)
            'reactions' => array_map(fn ($r) => [
                'id' => $r['id'],
                'emoji' => $r['emoji'] ?? null,
                'user' => $r['user'] ?? null,
            ], array_map(function ($item) {
                return is_object($item) ? (array) $item : $item;
            }, $reactions->map(function ($r) {
                return [
                    'id' => $r->id,
                    'emoji' => $r->emoji,
                    'user' => $r->user ? [
                        'id' => $r->user->id ?? null,
                        'full_name' => $r->user->getActivityLogUserName() ?? ($r->user->full_name ?? $r->user->name ?? $r->user->email ?? null),
                        'created_at' => $r->created_at->format(config('activity-log.datetime_format')),
                    ] : null,
                ];
            })->toArray())),
            'reactionGroups' => $groups,
            'reactionCounts' => $counts,
            'currentUserReaction' => $currentUserReaction,
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
            'content' => $this->resource->communicationLog->content ?? $this->resource->description,
            'icon' => CommunicationLog::ICON_TYPE_MAP[$this->resource->communicationLog->type],
            'date' => $this->resource->communicationLog->created_at
                ->setTimezone(request()->input('timezone', config('app.timezone', 'UTC')))
                ->format(config('activity-log.datetime_format')),
            'reads_count' => $this->resource->communicationLog->reads->count(),
            'read_at_date' => $this->resource->communicationLog->reads->last()?->created_at
                ?->setTimezone(request()->input('timezone', config('app.timezone', 'UTC')))
                ?->format(config('activity-log.datetime_format')),
        ];
    }
}

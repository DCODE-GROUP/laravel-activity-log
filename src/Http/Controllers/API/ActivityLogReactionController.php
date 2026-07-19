<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Dcodegroup\ActivityLog\Http\Requests\ExistingRequest;
use Dcodegroup\ActivityLog\Http\Services\ActivityLogService;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Models\ActivityLogReaction;
use Illuminate\Routing\Controller;

class ActivityLogReactionController extends Controller
{
    public function __construct(protected ActivityLogService $service) {}

    public function __invoke(ExistingRequest $request, ActivityLog $activity_log)
    {
        $emoji = $request->input('emoji');
        if (! $emoji) {
            return response()->json(['message' => 'emoji is required'], 422);
        }

        $userId = null;
        if ($request->filled('currentUser.id')) {
            $userId = $request->input('currentUser.id');
        } elseif (auth()->check()) {
            $userId = auth()->id();
        }

        $existing = ActivityLogReaction::query()
            ->where('activity_log_id', $activity_log->id)
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->where('emoji', $emoji)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            ActivityLogReaction::create([
                'activity_log_id' => $activity_log->id,
                'user_id' => $userId,
                'emoji' => $emoji,
            ]);
        }

        // return updated list for the same model that activity_log belongs to
        $modelClass = $activity_log->activitiable_type;
        $modelId = $activity_log->activitiable_id;
        $model = $modelClass::find($modelId);

        return $this->service->getActivityLogs($model);
    }
}

<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Dcodegroup\ActivityLog\Http\Requests\ExistingRequest;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Models\ActivityLogReaction;
use Dcodegroup\ActivityLog\Resources\ActivityLog as ActivityLogResource;
use Illuminate\Routing\Controller;

class ActivityLogReactionController extends Controller
{
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

        $existing = ActivityLogReaction::where('activity_log_id', $activity_log->id)
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->first();

        if ($existing) {
            if ($existing->emoji === $emoji) {
                // same emoji: remove reaction
                $existing->delete();
            } else {
                // different emoji: update existing reaction
                $existing->emoji = $emoji;
                $existing->save();
            }
        } else {
            ActivityLogReaction::create([
                'activity_log_id' => $activity_log->id,
                'user_id' => $userId,
                'emoji' => $emoji,
            ]);
        }

        return new ActivityLogResource(
            $activity_log->load([
                'user',
                'communicationLog.reads',
                'reactions.user',
            ])
        );
    }
}

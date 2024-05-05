<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Dcodegroup\ActivityLog\Http\Requests\ExistingRequest;
use Dcodegroup\ActivityLog\Mail\CommentNotification;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Resources\ActivityLogCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;

class CommentController extends Controller
{
    public function __invoke(ExistingRequest $request)
    {
        $modelClass = $request->input('modelClass');
        $modelId = $request->input('modelId');
        $model = $modelClass::find($modelId);
        $comment = $request->input('comment');
        if ($request->filled('comment') && $request->filled('currentUrl')) {
            $activity = resolve(config('activity-log.activity_log_model'))->query()->create([
                'activitiable_type' => $modelClass,
                'activitiable_id' => $modelId,
                'type' => ActivityLog::TYPE_COMMENT,
                'title' => 'left a comment.',
                'description' => $comment,
            ]);
            $url = $request->input('currentUrl').'#activity_'.$activity->id;
            $user = auth()->user()?->getActivityLogUserName() ?: 'System';
            $emailSubject = class_basename($modelClass).' #'.$modelId.' '.$user;
            $mentionedUsers = collect(explode(' ', trim($request->input('comment'))))->filter(function ($key) {
                return str_starts_with($key, '@');
            });

            foreach ($mentionedUsers as $key) {
                $email = substr($key, 1);
                $userModel = config('activity-log.user_model');
                $userSearch = config('activity-log.user_search');
                if ($userModel::query()->where($userSearch, $email)->exists()) {
                    Mail::to($email)->send(new CommentNotification($emailSubject, $url));
                    $comment = str_replace($key, '<a href="mailto:'.$email.'">'.$email.'</a>', $comment);
                }
            }
            $activity->update(['description' => $comment]);
        }

        $communication = config('activity-log.communication_log_relationship');

        return new ActivityLogCollection($model->activityLogs()
            ->with([
                config('activity-log.user_relationship'),
                $communication,
                "$communication.reads",
            ])->where(fn (Builder $builder) => $builder
            ->whereNull('communication_log_id')
            ->orWhere(fn (Builder $builder) => $builder
                ->whereNotNull('communication_log_id')
                ->whereNot('title', 'like', '% read an %')
                ->whereNot('title', 'like', '% view a %'))
            )
            ->orderByDesc('created_at')->get());
    }
}

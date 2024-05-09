<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Dcodegroup\ActivityLog\Http\Requests\ExistingRequest;
use Dcodegroup\ActivityLog\Http\Services\ActivityLogService;
use Dcodegroup\ActivityLog\Mail\CommentNotification;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Illuminate\Routing\Controller;

class CommentController extends Controller
{
    public function __construct(protected ActivityLogService $service)
    {
    }

    public function __invoke(ExistingRequest $request)
    {
        $modelClass = $request->input('modelClass');
        $modelId = $request->input('modelId');
        $model = $modelClass::find($modelId);
        if ($request->filled('comment') && $request->filled('currentUrl')) {
            $comment = $request->input('comment');
            $activity = resolve($this->service->activityLogModel)->query()->create([
                'activitiable_type' => $modelClass,
                'activitiable_id' => $modelId,
                'type' => ActivityLog::TYPE_COMMENT,
                'title' => 'left a comment.',
                'description' => $comment,
            ]);
            $url = $request->input('currentUrl').'#activity_'.$activity->id;
            $user = $request->filled('currentUser') ? $request->input('currentUser') : 'System';
            $emailSubject = class_basename($modelClass).' #'.$modelId.' '.$user;
            $email = new CommentNotification($emailSubject, $url, $model);

            $this->service->mentionUserInComment($comment, $activity, $email);
        }
        return $this->service->getActivityLogs($model);
    }
}

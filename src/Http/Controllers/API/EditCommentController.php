<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Dcodegroup\ActivityLog\Events\ActivityLogCommentUpdated;
use Dcodegroup\ActivityLog\Http\Requests\EditCommentRequest;
use Dcodegroup\ActivityLog\Http\Services\ActivityLogService;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Illuminate\Routing\Controller;

class EditCommentController extends Controller
{
    public function __construct(protected ActivityLogService $service) {}

    public function __invoke(ActivityLog $comment, EditCommentRequest $request)
    {
        $this->service->mentionUserInComment($request->input('comment'), $comment);
        $model = $comment->activitiable()->first();

        if (config('activity-log.attachment_model') && $model) {
            $this->service->syncAttachments($comment, $this->service->resolveAttachmentIds($request, $model));
        }

        /**
         * Where does the edit occur?
         */
        event(new ActivityLogCommentUpdated($comment));

        return $this->service->getActivityLogs($model);
    }
}

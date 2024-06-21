<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

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

        return $this->service->getActivityLogs($model);
    }
}

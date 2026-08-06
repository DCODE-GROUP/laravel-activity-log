<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Dcodegroup\ActivityLog\Events\ActivityLogCommentCreated;
use Dcodegroup\ActivityLog\Http\Requests\ExistingRequest;
use Dcodegroup\ActivityLog\Http\Services\ActivityLogService;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function __construct(protected ActivityLogService $service) {}

    public function __invoke(ExistingRequest $request)
    {
        $modelClass = $request->input('modelClass');
        $modelId = $request->input('modelId');
        $model = $modelClass::find($modelId);
        if ($request->filled('comment') && $request->filled('currentUrl')) {
            $comment = $request->input('comment');
            $attachmentIds = collect($request->input('attachment_ids', $request->input('attachments', [])))
                ->when($request->filled('attachment_id'), fn ($ids) => $ids->push($request->integer('attachment_id')))
                ->unique()
                ->values();

            $activity = DB::transaction(function () use ($modelClass, $modelId, $comment, $attachmentIds) {
                $activity = resolve($this->service->activityLogModel)->query()->create([
                    'activitiable_type' => $modelClass,
                    'activitiable_id' => $modelId,
                    'type' => ActivityLog::TYPE_COMMENT,
                    'title' => 'left a comment.',
                    'description' => $comment,
                ]);

                $activity->attachments()->createMany(
                    $attachmentIds->map(fn (int $attachmentId) => ['attachment_id' => $attachmentId])->all()
                );

                return $activity;
            });

            event(new ActivityLogCommentCreated($activity));
            $url = $request->input('currentUrl').'#activity_'.$activity->id;
            $user = $request->filled('currentUser') ? $request->input('currentUser') : 'System';
            $modelName = $model->activityLogEntityName();
            $emailSubject = __('activity-log.headings.subjects', ['model' => $user, 'entity' => $modelName]);
            $email = [
                'content' => $comment,
                'title' => $emailSubject,
                'action' => $url,
                'model' => $model,
                'modelName' => $modelName,
            ];
            $this->service->mentionUserInComment($comment, $activity, $email);
        }

        return $this->service->getActivityLogs($model, ActivityLog::TYPE_COMMENT);
    }
}

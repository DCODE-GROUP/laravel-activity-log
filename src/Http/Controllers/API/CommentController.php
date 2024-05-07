<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Dcodegroup\ActivityLog\Contracts\HasActivityUser;
use Dcodegroup\ActivityLog\Http\Requests\ExistingRequest;
use Dcodegroup\ActivityLog\Http\Services\ActivityLogService;
use Dcodegroup\ActivityLog\Mail\CommentNotification;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
        $comment = $request->input('comment');
        if ($request->filled('comment') && $request->filled('currentUrl')) {
            $activity = resolve($this->service->activityLogModel)->query()->create([
                'activitiable_type' => $modelClass,
                'activitiable_id' => $modelId,
                'type' => ActivityLog::TYPE_COMMENT,
                'title' => 'left a comment.',
                'description' => $comment,
            ]);
            $url = $request->input('currentUrl') . '#activity_' . $activity->id;
            $user = $request->filled('currentUser') ? $request->input('currentUser') : 'System';
            $emailSubject = class_basename($modelClass) . ' #' . $modelId . ' ' . $user;

            $regexp = '/@\[[^\]]*\]/';
            $mentionedUsers = Str::matchAll($regexp, trim($request->input('comment')));

            foreach ($mentionedUsers as $key) {
                $identiy = Str::replaceStart('@[', '', $key);
                $identiy = Str::replaceEnd(']', '', $identiy);

                $users = $this->service->userModel::query()
                    ->with($this->service->userSearchRelationship)
                    ->where(function (Builder $q) use ($identiy) {
                        foreach ($this->service->userSearchTerm as $field) {
                            $parts = explode('.', $field);
                            if (count($parts) > 1) {
                                [$relation, $relationField] = $parts;
                                $q->orWhereHas($relation, fn(Builder $builder) => $builder->where($relationField, $identiy));
                                continue;
                            }
                            $q->orWhere($field, $identiy);
                        }
                    })->get();

                /** @var HasActivityUser $userModel */
                foreach ($users as $userModel) {
                    $email = $userModel->getActivityLogEmail();
                    Mail::to($email)->send(new CommentNotification($emailSubject, $url));
                    $comment = str_replace($key, '<a href="mailto:' . $email . '">' . $userModel->getActivityLogUserName() . '</a>', $comment);
                }
            }
            $activity->update(['description' => $comment]);
        }

        return $this->service->getActivityLogs($model);
    }
}

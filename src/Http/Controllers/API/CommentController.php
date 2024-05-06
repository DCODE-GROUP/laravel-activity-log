<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Dcodegroup\ActivityLog\Contracts\HasActivityUser;
use Dcodegroup\ActivityLog\Http\Requests\ExistingRequest;
use Dcodegroup\ActivityLog\Mail\CommentNotification;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Resources\ActivityLogCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    protected $activityLogModel;

    protected $userModel;

    protected $userRelationship;

    protected $userSearchRelationship;

    protected $userSearchTerm;

    protected $communicationLogRelationship;

    public function __construct()
    {
        $this->activityLogModel = config('activity-log.activity_log_model', ActivityLog::class);
        $this->userModel = config('activity-log.user_model');
        $this->userRelationship = config('activity-log.user_relationship');
        $this->userSearchRelationship = config('activity-log.user_search_relationship', []);
        $this->userSearchTerm = config('activity-log.user_search_term', ['email']);
        $this->userSearchTerm = is_array($this->userSearchTerm) ? $this->userSearchTerm : [$this->userSearchTerm];
        $this->communicationLogRelationship = config('activity-log.communication_log_relationship');
    }
    public function __invoke(ExistingRequest $request)
    {
        $modelClass = $request->input('modelClass');
        $modelId = $request->input('modelId');
        $model = $modelClass::find($modelId);
        $comment = $request->input('comment');
        if ($request->filled('comment') && $request->filled('currentUrl')) {
            $activity = resolve($this->activityLogModel)->query()->create([
                'activitiable_type' => $modelClass,
                'activitiable_id' => $modelId,
                'type' => ActivityLog::TYPE_COMMENT,
                'title' => 'left a comment.',
                'description' => $comment,
            ]);
            $url = $request->input('currentUrl').'#activity_'.$activity->id;
            $user = $request->filled('currentUser') ? $request->input('currentUser') : 'System';
            $emailSubject = class_basename($modelClass).' #'.$modelId.' '.$user;

            $regexp = '/@\[[^\]]*\]/';
            $mentionedUsers = Str::matchAll($regexp, trim($request->input('comment')));

            foreach ($mentionedUsers as $key) {
                $identiy = Str::replaceStart("@[", "", $key);
                $identiy = Str::replaceEnd("]", "", $identiy);

                $users = $this->userModel::query()
                    ->with($this->userSearchRelationship)
                    ->where(function (Builder $q) use ($identiy) {
                    foreach ($this->userSearchTerm as $field) {
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
                    $comment = str_replace($key, '<a href="mailto:'.$email.'">'.$userModel->getActivityLogUserName().'</a>', $comment);
                }
            }
            $activity->update(['description' => $comment]);
        }

        return new ActivityLogCollection($model->activityLogs()
            ->with([
                $this->userRelationship,
                $this->communicationLogRelationship,
                $this->communicationLogRelationship.".reads",
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

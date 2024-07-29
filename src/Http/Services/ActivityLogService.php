<?php

namespace Dcodegroup\ActivityLog\Http\Services;

use Dcodegroup\ActivityLog\Mail\CommentNotification;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Resources\ActivityLogCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ActivityLogService
{
    public $activityLogModel;

    public $userModel;

    public $userRelationship;

    public $userSearchRelationship;

    public $userSearchTerm;

    public $communicationLogRelationship;

    public function __construct()
    {
        $this->activityLogModel = config('activity-log.activity_log_model');
        $this->userModel = config('activity-log.user_model');
        $this->userRelationship = config('activity-log.user_relationship');
        $this->userSearchRelationship = config('activity-log.user_search_relationship');
        $this->userSearchTerm = config('activity-log.user_search_term');
        $this->userSearchTerm = is_array($this->userSearchTerm) ? $this->userSearchTerm : [$this->userSearchTerm];
        $this->communicationLogRelationship = config('activity-log.communication_log_relationship');
    }

    public function getActivityLogs($model): ActivityLogCollection
    {
        return new ActivityLogCollection($model->activityLogs()
            ->with([
                $this->userRelationship,
                $this->communicationLogRelationship,
                $this->communicationLogRelationship.'.reads',
            ])->where(fn (Builder $builder) => $builder
            ->whereNull('communication_log_id')
            ->orWhere(fn (Builder $builder) => $builder
                ->whereNotNull('communication_log_id')
                ->whereNot('title', 'like', '% read an %')
                ->whereNot('title', 'like', '% view a %'))
            )
            ->orderByDesc('created_at')->get());
    }

    public function mentionUserInComment(string $comment, ActivityLog $activityLog, ?array $mailable = null): ActivityLog
    {
        $activityLog->update(['meta' => $comment], ['timestamps' => false]);
        $regexp = '/@\[[^\]]*\]/';
        $mentionedUsers = Str::matchAll($regexp, trim($comment));
        foreach ($mentionedUsers as $key) {
            $identiy = Str::replaceStart('@[', '', $key);
            $identiy = Str::replaceEnd(']', '', $identiy);

            $this->userModel::query()
                ->with($this->userSearchRelationship)
                ->where(function (Builder $q) use ($identiy) {
                    foreach ($this->userSearchTerm as $field) {
                        if (is_array($field)) {
                            $query = 'concat(';
                            foreach ($field as $item) {
                                $query .= collect($field)->first() !== $item ? $item : $item.", ' ', ";
                            }
                            $query .= ')';
                            $q->orWhere(DB::raw($query), $identiy);

                            continue;
                        }
                        $parts = explode('.', $field);
                        if (count($parts) > 1) {
                            [$relation, $relationField] = $parts;
                            $q->orWhereHas($relation, fn (Builder $builder) => $builder->where($relationField, $identiy));

                            continue;
                        }
                        $q->orWhere($field, $identiy);
                    }
                })->get()->each(function ($userModel) use ($mailable, $key, &$comment) {
                    $email = $userModel->getActivityLogEmail();

                    if ($mailable) {
                        $model = $mailable['model'];
                        $data = [
                            'content' => $mailable['content'],
                            'title' => $mailable['title'],
                            'modelName' => $mailable['modelName'],
                        ];
                        if (method_exists($model, 'getActivityLogEmails') && ! in_array($email, $model->getActivityLogEmails())) {
                            // @phpstan-ignore-next-line
                            $data['action'] = ! empty($model->getMentionCommentUrl($userModel)) ? $model->getMentionCommentUrl($userModel) : $mailable['action'];
                        }
                        Mail::to($email)->send(new CommentNotification($data, $model));
                    }
                    $to = is_array($email) ? implode(', ', $email) : $email;
                    $comment = str_replace($key, '<a class="activity__comment--tag" href="mailto:'.$to.'">@'.$userModel->getActivityLogUserName().'</a>', $comment);
                });
        }
        $activityLog->update(['description' => $comment], ['timestamps' => false]);

        return $activityLog;
    }
}

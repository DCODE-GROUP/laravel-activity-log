<?php

namespace Dcodegroup\ActivityLog\Http\Services;

use Dcodegroup\ActivityLog\Contracts\HasActivityUser;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Resources\ActivityLogCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Mail\Mailable;
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
                $this->communicationLogRelationship . '.reads',
            ])->where(fn(Builder $builder) => $builder
                ->whereNull('communication_log_id')
                ->orWhere(fn(Builder $builder) => $builder
                    ->whereNotNull('communication_log_id')
                    ->whereNot('title', 'like', '% read an %')
                    ->whereNot('title', 'like', '% view a %'))
            )
            ->orderByDesc('created_at')->get());
    }

    public function mentionUserInComment(string $comment, ActivityLog $activityLog, Mailable|null $mailable = null): ActivityLog
    {
        $regexp = '/@\[[^\]]*\]/';
        $mentionedUsers = Str::matchAll($regexp, trim($comment));
        foreach ($mentionedUsers as $key) {
            $identiy = Str::replaceStart('@[', '', $key);
            $identiy = Str::replaceEnd(']', '', $identiy);

            $users = $this->userModel::query()
                ->with($this->userSearchRelationship)
                ->where(function (Builder $q) use ($identiy) {
                    foreach ($this->userSearchTerm as $field) {
                        if (is_array($field)) {
                            $query = "concat(";
                            foreach ($field as $item) {
                                $query .= collect($field)->last() === $item ? $item : $item . ", ' ', ";
                            }
                            $query .= ")";
                            $q->orWhere(DB::raw($query), $identiy);
                            continue;
                        }
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
                if ($mailable) {
                    Mail::to($email)->send($mailable);
                }
                $comment = str_replace($key, '<a href="mailto:' . $email . '">' . $userModel->getActivityLogUserName() . '</a>', $comment);
            }
        }
        $activityLog->update(['description' => $comment]);
        return $activityLog;
    }
}

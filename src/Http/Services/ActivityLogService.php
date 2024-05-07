<?php

namespace Dcodegroup\ActivityLog\Http\Services;

use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Resources\ActivityLogCollection;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogService
{
    public $activityLogModel;

    public $userModel;

    public $userRelationship;

    public $userSearchRelationship;

    public $userSearchTerm;

    public $communicationLogRelationship;

    public function __construct() {
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
}
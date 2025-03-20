<?php

namespace Dcodegroup\ActivityLog\Events;

use Dcodegroup\ActivityLog\Models\ActivityLog;

class ActivityLogCommentDeleted
{
    public function __construct(public ActivityLog $activityLog) {}
}

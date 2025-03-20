<?php

namespace Dcodegroup\ActivityLog\Events;

use Dcodegroup\ActivityLog\Models\ActivityLog;

class ActivityLogCommentUpdated
{
    public function __construct(public ActivityLog $activityLog) {}
}

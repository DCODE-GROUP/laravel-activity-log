<?php

namespace Dcodegroup\ActivityLog\Events;

use Dcodegroup\ActivityLog\Models\ActivityLog;

class ActivityLogCommentCreated
{
    public function __construct(public ActivityLog $activityLog) {}
}

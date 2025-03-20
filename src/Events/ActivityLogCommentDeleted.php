<?php

namespace Dcodegroup\ActivityLog\Events;

use Dcodegroup\ActivityLog\Models\ActivityLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityLogCommentDeleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public ActivityLog $activityLog) {}
}

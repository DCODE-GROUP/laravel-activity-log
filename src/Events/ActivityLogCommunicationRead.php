<?php

namespace Dcodegroup\ActivityLog\Events;

use Dcodegroup\ActivityLog\Models\ActivityLog;

class ActivityLogCommunicationRead
{
    public function __construct(public ActivityLog $activityLog) {}
}

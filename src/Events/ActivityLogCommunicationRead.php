<?php

namespace Dcodegroup\ActivityLog\Events;

use Dcodegroup\ActivityLog\Models\CommunicationLog;

class ActivityLogCommunicationRead
{
    public function __construct(public CommunicationLog $communicationLog) {}
}

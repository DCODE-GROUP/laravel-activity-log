<?php

namespace Dcodegroup\ActivityLog\Events;

use Dcodegroup\ActivityLog\Models\CommunicationLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityLogCommunicationRead
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public CommunicationLog $communicationLog) {}
}

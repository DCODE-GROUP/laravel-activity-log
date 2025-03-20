<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Dcodegroup\ActivityLog\Events\ActivityLogCommunicationRead;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Models\CommunicationLog;
use Illuminate\Routing\Controller;

class ReadEmailController extends Controller
{
    public function __construct() {}

    public function __invoke(ActivityLog $activityLog)
    {
        if (! auth()->check()) {
            if ($activityLog->communication_log_id) {
                $activityLog->replicate()->fill([
                    'title' => __('activity-log.words.read_email'),
                    'description' => '',
                    'communication_log_id' => $activityLog->communication_log_id,
                    'type' => ActivityLog::TYPE_NOTIFICATION,
                ])->save();

                $communicationLog = CommunicationLog::query()->find($activityLog->communication_log_id);
                if ($communicationLog instanceof CommunicationLog) {
                    event(new ActivityLogCommunicationRead($communicationLog));
                }

            }
        }
    }
}

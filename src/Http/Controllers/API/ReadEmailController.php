<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Dcodegroup\ActivityLog\Events\ActivityLogCommunicationRead;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Illuminate\Routing\Controller;

class ReadEmailController extends Controller
{
    public function __construct() {}

    public function __invoke(ActivityLog $activityLog)
    {
        if (! auth()->check()) {
            if (! empty($activityLog->communication_log_id)) {
                $activityLog->replicate()->fill([
                    'title' => __('activity-log.words.read_email'),
                    'description' => '',
                    'communication_log_id' => $activityLog->communication_log_id,
                    'type' => ActivityLog::TYPE_NOTIFICATION,
                ])->save();

                event(new ActivityLogCommunicationRead($activityLog));
            }
        }
    }
}

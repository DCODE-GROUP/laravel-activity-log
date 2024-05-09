<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;


use Dcodegroup\ActivityLog\Models\ActivityLog;
use Illuminate\Routing\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReadEmailController extends Controller
{
    public function __construct()
    {
    }

    public function __invoke(ActivityLog $activityLog): BinaryFileResponse
    {
        if (! auth()->check()) {
            if ($activityLog->communication_log_id) {
                $activityLog->replicate()->fill([
                    'title' => __('activity-log.words.read_email'),
                    'description' => '',
                    'communication_log_id' => $activityLog->communication_log_id,
                    'type' => ActivityLog::TYPE_NOTIFICATION,
                ])->save();
            }
        }

        return response()->file('images/stealth-pixel.png');
    }
}

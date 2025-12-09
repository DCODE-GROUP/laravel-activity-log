<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use App\Mail\CustomHtmlMail;
use Dcodegroup\ActivityLog\Events\ActivityLogCommentCreated;
use Dcodegroup\ActivityLog\Http\Requests\ExistingRequest;
use Dcodegroup\ActivityLog\Http\Services\ActivityLogService;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Models\CommunicationLog;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;

class ResendCommunicationController extends Controller
{
    public function __invoke(CommunicationLog $communicationLog)
    {
        $mail = Mail::to($communicationLog->to);

        if ($communicationLog->cc) {
            $mail->cc(explode(',', $communicationLog->cc));
        }

        if ($communicationLog->bcc) {
            $mail->bcc(explode(',', $communicationLog->bcc));
        }

        $mail->send(new CustomHtmlMail($communicationLog));

        return response()->json([
            'message' => 'Resent successfully',
        ]);
    }
}

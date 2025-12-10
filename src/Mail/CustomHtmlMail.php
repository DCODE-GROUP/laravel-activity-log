<?php

namespace Dcodegroup\ActivityLog\Mail;

use Dcodegroup\ActivityLog\Models\CommunicationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomHtmlMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(protected CommunicationLog $communicationLog) {}

    public function build()
    {
        return $this->subject($this->communicationLog->subject ?? ' ')
            ->html($this->communicationLog->content ?? ' ');
    }
}

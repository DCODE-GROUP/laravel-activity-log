<?php

namespace Dcodegroup\ActivityLog\Mail;

use Dcodegroup\ActivityLog\Models\CommunicationLog;
use Dcodegroup\ActivityLog\Support\Traits\ReadMailableTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomHtmlMail extends Mailable
{
    use Queueable;
    use ReadMailableTrait;
    use SerializesModels;

    public function __construct(protected CommunicationLog $communicationLog) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->communicationLog->subject);
    }

    /**
     * Get the message content definition.
     */
    public function prepareContent(): Content
    {
        return new Content(htmlString: $this->communicationLog->content);
    }
}

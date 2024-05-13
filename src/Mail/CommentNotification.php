<?php

namespace Dcodegroup\ActivityLog\Mail;

use Dcodegroup\ActivityLog\Support\Traits\ReadMailableTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommentNotification extends Mailable
{
    use Queueable;
    use ReadMailableTrait;
    use SerializesModels;

    public function __construct(protected array $emailContent, protected $entityModel)
    {
        $this->model = $this->entityModel;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->emailContent['title']);
    }

    /**
     * Get the message content definition.
     */
    public function prepareContent(): Content
    {
        return new Content(markdown: config('activity-log.comment_email_template'), with: $this->emailContent);
    }
}

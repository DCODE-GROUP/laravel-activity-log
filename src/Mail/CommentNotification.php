<?php

namespace Dcodegroup\ActivityLog\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommentNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(protected string $subjectModel, protected string $url)
    {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(subject: __('activity-log.headings.subjects', ['model' => $this->subjectModel]));
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(markdown: config('activity-log.comment_email_template'), with: [
            'content' => $this->subjectModel,
            'action' => $this->url,
        ]);
    }
}

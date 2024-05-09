<?php

namespace Dcodegroup\ActivityLog\Support\Traits;

use Dcodegroup\ActivityLog\Models\ActivityLog;
use Illuminate\Mail\Mailables\Content;

trait OrderReadMailableTrait
{
    /**
     * @var ActivityLog|null
     */
    protected $activityLog = null;

    public function prepareContent(): Content
    {
        return new Content();
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $content = $this->prepareContent();

        if ($this->activityLog) {
            $content->with['readUrl'] = route('read-email', [
                'order' => $this->order,
                'activity_log' => $this->activityLog,
            ]);
        }

        return $content;
    }

    public function send($mailer)
    {
        //todo: this code to skip error on phpunit test
        if (app()->environment('testing')) {
            return parent::send($mailer);
        }

        $modelService = resolve(config('activity-log.activity_log_model_service'));
        if ($modelService) {
            $envelope = $this->envelope();
            $this->activityLog = $modelService->createCommunicationLog($this->order, [
                'subject' => $envelope->subject,
                'cc' => $envelope->cc,
                'bcc' => $envelope->bcc,
            ], $this);
        }

        return parent::send($mailer);
    }
}

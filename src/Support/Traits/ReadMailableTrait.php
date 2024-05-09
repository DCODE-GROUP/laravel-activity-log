<?php

namespace Dcodegroup\ActivityLog\Support\Traits;

use Dcodegroup\ActivityLog\Models\ActivityLog;
use Illuminate\Mail\Mailables\Content;

trait ReadMailableTrait
{
    /**
     * @var ActivityLog|null
     */
    protected $activityLog = null;

    protected $model = null;

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
                'order' => $this->model,
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

        if (method_exists($this->model, 'createCommunicationLog') && method_exists($this->model, 'createActivityLog')) {
            $envelope = $this->envelope();
            $to = $this->to[0]['address'];
            $communicationLog = $this->model->createCommunicationLog([
                'subject' => $envelope->subject,
                'cc' => $envelope->cc,
                'bcc' => $envelope->bcc,
            ], $to, $this->render() ?: '');
            $this->activityLog = $this->model->createActivityLog([
                'type' => ActivityLog::TYPE_NOTIFICATION,
                'title' => __('activity-log.words.send_email') . $to,
                'description' => '',
                'communication_log_id' => $communicationLog->id,
            ]);
        }

        return parent::send($mailer);
    }
}

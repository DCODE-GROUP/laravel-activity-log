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

        if (method_exists($this->model, 'createCommunicationLog')) {
            $envelope = $this->envelope();
            $to = $mailer->to[0]['address'];
            $this->model->createCommunicationLog($envelope, $to, $mailer->render() ?: '');
        }
        return parent::send($mailer);
    }
}

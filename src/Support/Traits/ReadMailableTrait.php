<?php

namespace Dcodegroup\ActivityLog\Support\Traits;

use Dcodegroup\ActivityLog\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailables\Content;

trait ReadMailableTrait
{
    /**
     * @var ActivityLog|null
     */
    protected $activityLog = null;

    protected $model = null;

    public Model $mailableModel;

    public function prepareContent(): Content
    {
        return new Content;
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $content = $this->prepareContent();
        if ($this->activityLog) {
            $content->with['readUrl'] = route(config('activity-log.route_name').'.read-email', [
                'activity_log' => $this->activityLog,
            ]);
        }

        return $content;
    }

    public function send($mailer)
    {
        $this->mailableModel = $this->model;

        return parent::send($mailer);
    }
}

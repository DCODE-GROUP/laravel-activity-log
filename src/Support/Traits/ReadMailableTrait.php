<?php

namespace Dcodegroup\ActivityLog\Support\Traits;

use Dcodegroup\ActivityLog\Models\ActivityLog;
use Illuminate\Container\Container;
use Illuminate\Mail\Mailables\Content;

trait ReadMailableTrait
{
    /**
     * @var ActivityLog|null
     */
    protected $activityLog = null;

    protected $model = null;

    public function send($mailer)
    {
        // todo: this code to skip error on phpunit test
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
            ], $to, $this->logRender() ?: '');
            $this->activityLog = $this->model->createActivityLog([
                'type' => ActivityLog::TYPE_NOTIFICATION,
                'title' => __('activity-log.words.send_email').$to,
                'description' => '',
                'communication_log_id' => $communicationLog->id,
            ]);
        }

        return parent::send($mailer);
    }

    private function logRender()
    {
        $logMailable = clone $this;

        return $logMailable->withLocale($logMailable->locale, function () use ($logMailable) {
            if (method_exists($logMailable, 'build')) {
                Container::getInstance()->call([$logMailable, 'build']);
            }

            //            $this->activityLogEnsureContentIsHydrated($logMailable);

            return Container::getInstance()->make('mailer')->render(
                $logMailable->buildView(), $logMailable->buildViewData()
            );
        });

    }

    private function activityLogEnsureContentIsHydrated($logMailable)
    {
        if (! method_exists($logMailable, 'content')) {
            return;
        }

        $content = $logMailable->content();

        if ($content->view) {
            $this->view($content->view);
        }

        if ($content->html) {
            $this->view($content->html);
        }

        if ($content->text) {
            $this->text($content->text);
        }

        if ($content->markdown) {
            $this->markdown($content->markdown);
        }

        if ($content->htmlString) {
            $this->html($content->htmlString);
        }

        foreach ($content->with as $key => $value) {
            $this->with($key, $value);
        }
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

    public function prepareContent(): Content
    {
        return new Content;
    }
}
